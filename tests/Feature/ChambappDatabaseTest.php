<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\PriceType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\JobRequest;
use App\Models\Message;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ChambappDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_phase_two_schema_is_present(): void
    {
        foreach ([
            'users', 'professional_profiles', 'categories', 'services', 'service_images',
            'job_requests', 'conversations', 'messages', 'payments', 'payment_transactions',
            'job_quotes', 'reviews', 'job_disputes', 'favorites', 'notifications', 'reports', 'admin_audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }

        $this->assertTrue(Schema::hasColumns('users', ['phone', 'role', 'status']));
        $this->assertTrue(Schema::hasColumns('services', ['price', 'price_type', 'deleted_at']));
        $this->assertTrue(Schema::hasColumns('payments', ['gross_amount', 'platform_fee', 'professional_amount']));
        $this->assertTrue(Schema::hasColumns('payments', ['platform_fee_percent', 'external_preference_id', 'checkout_url']));
        $this->assertTrue(Schema::hasColumns('professional_profiles', ['mercadopago_user_id', 'mercadopago_access_token', 'mercadopago_refresh_token']));
    }

    public function test_models_cast_enums_and_keep_the_main_relationship_graph(): void
    {
        $professional = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $professional->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]);
        $client = User::factory()->client()->create();
        $category = Category::factory()->create();
        $service = Service::factory()->for($profile, 'professional')->for($category)->create([
            'price_type' => PriceType::FIXED,
        ]);
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'service_id' => $service->id,
            'status' => JobStatus::COMPLETED,
        ]);
        $conversation = Conversation::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $profile->id,
        ]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'status' => PaymentStatus::APPROVED,
        ]);
        $review = Review::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'rating' => 5,
        ]);

        $this->assertSame(UserRole::PROFESSIONAL, $professional->fresh()->role);
        $this->assertSame(VerificationStatus::VERIFIED, $profile->fresh()->verification_status);
        $this->assertSame(PriceType::FIXED, $service->fresh()->price_type);
        $this->assertSame(JobStatus::COMPLETED, $job->fresh()->status);
        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertSame(5, $review->fresh()->rating);

        $this->assertTrue($profile->fresh()->user->is($professional));
        $this->assertTrue($service->fresh()->professional->is($profile));
        $this->assertTrue($service->fresh()->category->is($category));
        $this->assertTrue($job->fresh()->client->is($client));
        $this->assertTrue($job->fresh()->professional->is($profile));
        $this->assertTrue($job->fresh()->service->is($service));
        $this->assertTrue($job->fresh()->conversation->is($conversation));
        $this->assertTrue($job->fresh()->payment->is($payment));
        $this->assertTrue($job->fresh()->review->is($review));
        $this->assertTrue($conversation->fresh()->messages->contains($message));
        $this->assertTrue($payment->fresh()->jobRequest->is($job));
    }

    public function test_favorites_and_job_reviews_enforce_unique_constraints(): void
    {
        $client = User::factory()->client()->create();
        $profile = ProfessionalProfile::factory()->create();
        $favoriteData = ['user_id' => $client->id, 'professional_id' => $profile->id];
        Favorite::create($favoriteData);

        try {
            Favorite::create($favoriteData);
            $this->fail('The favorite unique constraint was not enforced.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
        ]);
        $reviewData = [
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'rating' => 4,
        ];
        Review::create($reviewData);

        try {
            Review::create($reviewData);
            $this->fail('The review unique constraint was not enforced.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    public function test_reviews_reject_ratings_outside_the_domain(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Review::create(['rating' => 6]);
    }

    public function test_sensitive_user_attributes_are_hidden_from_serialization(): void
    {
        $user = User::factory()->create();
        $serialized = $user->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }
}
