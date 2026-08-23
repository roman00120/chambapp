<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google.client_id', 'public-client-id');
        config()->set('services.google.client_secret', 'test-secret');
        config()->set('services.google.redirect', 'http://127.0.0.1:8000/auth/google/callback');
    }

    public function test_google_redirect_stores_only_an_allowed_account_type(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(new RedirectResponse('https://accounts.google.test/oauth'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.redirect', ['account_type' => 'professional']))
            ->assertRedirect('https://accounts.google.test/oauth');

        $this->assertSame('professional', session('google_account_type'));
        $this->get(route('auth.google.redirect', ['account_type' => 'admin']))->assertStatus(422);
    }

    public function test_google_callback_registers_a_professional_and_creates_profile(): void
    {
        $this->mockGoogleUser($this->googleUser('google-123', 'PRO@Example.com', 'Ana Profesional'));

        $this->withSession(['google_account_type' => 'professional'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('professional.dashboard'));

        $user = User::where('email', 'pro@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::PROFESSIONAL, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('professional_profiles', ['user_id' => $user->id]);
    }

    public function test_google_callback_links_an_existing_email_without_changing_its_role(): void
    {
        $user = User::factory()->client()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);
        $this->mockGoogleUser($this->googleUser('google-existing', $user->email, 'Nombre Google'));

        $this->withSession(['google_account_type' => 'professional'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('client.dashboard'));

        $this->assertSame(UserRole::CLIENT, $user->fresh()->role);
        $this->assertSame('google-existing', $user->fresh()->google_id);
    }

    public function test_blocked_account_cannot_login_with_google(): void
    {
        $user = User::factory()->client()->create([
            'email' => 'blocked-google@example.com',
            'google_id' => 'google-blocked',
            'status' => UserStatus::BLOCKED,
        ]);
        $this->mockGoogleUser($this->googleUser('google-blocked', $user->email, $user->name));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cancelled_google_login_returns_to_login_with_a_friendly_error(): void
    {
        Socialite::shouldReceive('driver')->once()->with('google')->andThrow(new RuntimeException('access_denied'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function mockGoogleUser(GoogleUser $googleUser): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    private function googleUser(string $id, string $email, string $name): GoogleUser
    {
        return (new GoogleUser)->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'nickname' => null,
            'avatar' => 'https://images.google.test/avatar.png',
        ]);
    }
}
