<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_and_professional_can_register_but_admin_cannot(): void
    {
        foreach (['client', 'professional'] as $role) {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'Persona '.$role,
                'email' => $role.'@example.test',
                'phone' => '5512345678',
                'role' => $role,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'device_name' => 'Test device',
            ]);
            $response->assertCreated()
                ->assertJsonPath('data.user.role', $role)
                ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'role', 'status']]])
                ->assertJsonMissingPath('data.user.password')
                ->assertJsonMissingPath('data.user.remember_token');
        }

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Admin falso',
            'email' => 'admin-falso@example.test',
            'phone' => '5512345678',
            'role' => 'admin',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'Test device',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('users', ['email' => 'admin-falso@example.test']);
    }

    public function test_login_me_logout_and_revoked_token_flow(): void
    {
        $user = User::factory()->client()->create(['email' => 'client@example.test']);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'client@example.test',
            'password' => 'password',
            'device_name' => 'Pixel 9',
        ])->assertOk()->assertJsonPath('data.user.id', $user->id);
        $token = $login->json('data.token');

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonMissingPath('data.password');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        app('auth')->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_invalid_login_and_blocked_account_are_rejected(): void
    {
        $user = User::factory()->client()->create(['email' => 'blocked@example.test']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'incorrect',
            'device_name' => 'iPhone',
        ])->assertUnauthorized()->assertJsonPath('code', 'INVALID_CREDENTIALS');

        $token = $user->createToken('Existing device')->plainTextToken;
        $user->forceFill(['status' => UserStatus::BLOCKED])->save();
        $this->withToken($token)->getJson('/api/v1/me')
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_UNAVAILABLE');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_all_revokes_every_device(): void
    {
        $user = User::factory()->client()->create();
        $first = $user->createToken('Phone')->plainTextToken;
        $user->createToken('Tablet');

        $this->withToken($first)->postJson('/api/v1/auth/logout-all')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
