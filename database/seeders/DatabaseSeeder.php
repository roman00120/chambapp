<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\JobRequest;
use App\Models\Message;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \LogicException('DatabaseSeeder contiene datos demo y no puede ejecutarse en producción.');
        }

        $this->call(CategorySeeder::class);

        $categories = Category::query()->orderBy('sort_order')->get();
        $admin = User::factory()->admin()->create([
            'name' => 'Administrador Chambapp',
            'email' => 'admin@chambapp.local',
        ]);
        $clients = User::factory()->count(5)->client()->create();
        $professionalUsers = User::factory()->count(5)->professional()->create();
        $profiles = $professionalUsers->map(fn (User $user) => ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]));

        foreach ($profiles as $profile) {
            foreach ($categories->random(2) as $category) {
                Service::factory()->create([
                    'professional_id' => $profile->id,
                    'category_id' => $category->id,
                    'title' => $category->name.' profesional',
                    'slug' => 'servicio-'.$profile->id.'-'.$category->id,
                ]);
            }
        }

        foreach (range(0, 5) as $index) {
            $profile = $profiles->values()->get($index % $profiles->count());
            $service = $profile->services()->first();
            $client = $clients->values()->get($index % $clients->count());
            $status = match ($index) {
                0 => JobStatus::COMPLETED,
                1 => JobStatus::ACCEPTED,
                default => JobStatus::PENDING,
            };

            $job = JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'service_id' => $service?->id,
                'title' => $service?->title ?? 'Solicitud personalizada',
                'status' => $status,
                'accepted_at' => $status !== JobStatus::PENDING ? now()->subDays(4) : null,
                'started_at' => $status === JobStatus::COMPLETED ? now()->subDays(2) : null,
                'completed_at' => $status === JobStatus::COMPLETED ? now()->subDay() : null,
            ]);

            $conversation = Conversation::create([
                'job_request_id' => $job->id,
                'client_id' => $client->id,
                'professional_id' => $profile->id,
            ]);
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $client->id,
                'message' => 'Hola, me gustaría conocer más detalles del servicio.',
            ]);
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $profile->user_id,
                'message' => 'Claro, con gusto te ayudo.',
            ]);

            if ($status === JobStatus::COMPLETED) {
                Payment::create([
                    'job_request_id' => $job->id,
                    'client_id' => $client->id,
                    'professional_id' => $profile->id,
                    'provider' => null,
                    'currency' => 'MXN',
                    'gross_amount' => '650.00',
                    'platform_fee' => '65.00',
                    'provider_fee' => '20.00',
                    'professional_amount' => '565.00',
                    'status' => PaymentStatus::APPROVED,
                    'paid_at' => now()->subDay(),
                ]);
                Review::create([
                    'job_request_id' => $job->id,
                    'client_id' => $client->id,
                    'professional_id' => $profile->id,
                    'rating' => 5,
                    'comment' => 'Excelente atención y trabajo.',
                ]);
                $profile->update([
                    'average_rating' => '5.00',
                    'total_reviews' => 1,
                    'total_completed_jobs' => 1,
                ]);
            }
        }

        $clients->first()->favorites()->create([
            'professional_id' => $profiles->first()->id,
        ]);

        unset($admin);
    }
}
