<?php

namespace Tests\Feature\Api\V1;

use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_mobile_admin_api(): void
    {
        $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
        Sanctum::actingAs(User::factory()->client()->create());
        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }

    public function test_admin_reads_dashboard_and_manages_users_safely(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create(['name' => 'Cliente móvil']);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard')
            ->assertOk()->assertJsonPath('data.clients', 1)->assertJsonPath('data.users', 2);
        $this->getJson('/api/v1/admin/users?q=Cliente')
            ->assertOk()->assertJsonPath('data.0.id', $client->id);
        $this->patchJson('/api/v1/admin/users/'.$client->id.'/status', ['status' => 'suspended'])
            ->assertOk()->assertJsonPath('data.status', 'suspended');
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'user.status_changed', 'subject_id' => $client->id]);
        $this->patchJson('/api/v1/admin/users/'.$admin->id.'/status', ['status' => 'blocked'])
            ->assertUnprocessable();
    }

    public function test_admin_lists_and_verifies_professionals(): void
    {
        $admin = User::factory()->admin()->create();
        $professional = ProfessionalProfile::factory()->create(['verification_status' => VerificationStatus::PENDING]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/professionals?verification=pending')
            ->assertOk()->assertJsonPath('data.0.id', $professional->id);
        $this->patchJson('/api/v1/admin/professionals/'.$professional->id.'/verification', ['status' => 'verified'])
            ->assertOk()->assertJsonPath('data.verification_status', 'verified');
        $this->assertSame($admin->id, $professional->fresh()->verified_by);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'professional.verification_verified', 'subject_id' => $professional->id]);
    }

    public function test_admin_mobile_operations_cover_all_web_sections_and_audit_mutations(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['is_active' => true]);
        $service = Service::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        Sanctum::actingAs($admin);

        foreach (['categories', 'services', 'jobs', 'payments', 'commissions', 'reports', 'reviews', 'disputes'] as $section) {
            $this->getJson('/api/v1/admin/operations/'.$section)->assertOk()->assertJsonStructure(['data', 'meta']);
        }

        $this->patchJson('/api/v1/admin/categories/'.$category->id.'/toggle')->assertOk();
        $this->assertFalse($category->fresh()->is_active);
        $this->patchJson('/api/v1/admin/services/'.$service->id.'/moderate', ['action' => 'deactivate'])->assertOk();
        $this->assertFalse($service->fresh()->is_active);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'category.status_changed', 'subject_id' => $category->id]);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'service.deactivate', 'subject_id' => $service->id]);
    }
}
