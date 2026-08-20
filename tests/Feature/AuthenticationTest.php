<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_register_without_a_professional_profile(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => '  Ana   López ',
            'email' => ' ANA@EXAMPLE.COM ',
            'phone' => '55 1234 5678',
            'account_type' => 'client',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'ana@example.com')->firstOrFail();

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::CLIENT, $user->role);
        $this->assertSame(UserStatus::ACTIVE, $user->status);
        $this->assertDatabaseMissing('professional_profiles', ['user_id' => $user->id]);
        $this->assertTrue(Hash::check('password', $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_a_professional_registration_creates_the_profile_atomically(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Carlos Profesional',
            'email' => 'carlos@example.com',
            'phone' => '+52 55 5555 5555',
            'account_type' => 'professional',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'carlos@example.com')->firstOrFail();

        $response->assertRedirect(route('professional.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::PROFESSIONAL, $user->role);
        $this->assertDatabaseHas('professional_profiles', [
            'user_id' => $user->id,
            'verification_status' => 'unverified',
        ]);
    }

    public function test_public_registration_cannot_assign_admin_role(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Intento Admin',
            'email' => 'attacker@example.com',
            'phone' => '55 1234 5678',
            'account_type' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register'))->assertSessionHasErrors('account_type');
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        $client = User::factory()->client()->create(['email' => 'client-login@example.com']);
        $professional = User::factory()->professional()->create(['email' => 'pro-login@example.com']);
        $admin = User::factory()->admin()->create(['email' => 'admin-login@example.com']);

        $this->post(route('login.store'), ['email' => $client->email, 'password' => 'password'])
            ->assertRedirect(route('client.dashboard'));
        $this->post(route('logout'));

        $this->post(route('login.store'), ['email' => $professional->email, 'password' => 'password'])
            ->assertRedirect(route('professional.dashboard'));
        $this->post(route('logout'));

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_invalid_login_does_not_authenticate(): void
    {
        User::factory()->client()->create(['email' => 'wrong-password@example.com']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'wrong-password@example.com',
            'password' => 'incorrect',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $this->actingAs(User::factory()->client()->create());

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_users_cannot_access_another_role_dashboard(): void
    {
        $client = User::factory()->client()->create();
        $professional = User::factory()->professional()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($client)->get(route('professional.dashboard'))->assertForbidden();
        $this->actingAs($client)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($professional)->get(route('client.dashboard'))->assertForbidden();
        $this->actingAs($professional)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('client.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('professional.dashboard'))->assertForbidden();
    }

    public function test_guest_access_is_redirected_to_login(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('login'));
        $this->get(route('professional.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_suspended_or_blocked_users_cannot_login_or_use_the_platform(): void
    {
        $suspended = User::factory()->client()->suspended()->create(['email' => 'suspended@example.com']);
        $blocked = User::factory()->client()->create([
            'email' => 'blocked@example.com',
            'status' => UserStatus::BLOCKED,
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $suspended->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $blocked->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($suspended)
            ->get(route('client.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_email_verification_can_be_requested_and_completed(): void
    {
        Notification::fake();
        $user = User::factory()->client()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))->assertOk();
        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect();
        Notification::assertSentTo($user, VerifyEmail::class);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('client.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_password_reset_link_and_reset_flow_work(): void
    {
        Notification::fake();
        $user = User::factory()->client()->create(['email' => 'reset@example.com']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_login_is_rate_limited(): void
    {
        $email = 'rate-limit@example.com';
        User::factory()->client()->create(['email' => $email]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $email, 'password' => 'incorrect']);
        }

        $this->post(route('login.store'), ['email' => $email, 'password' => 'incorrect'])
            ->assertStatus(429);
    }

    public function test_authentication_forms_include_csrf_tokens(): void
    {
        $this->get(route('login'))->assertSee('name="_token"', false);
        $this->get(route('register'))->assertSee('name="_token"', false);
        $this->get(route('password.request'))->assertSee('name="_token"', false);
    }
}
