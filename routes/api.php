<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AdminOperationsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProfessionalController;
use App\Http\Controllers\Api\V1\ProfessionalIdentityVerificationController;
use App\Http\Controllers\Api\V1\PublicController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'api_version' => 'v1']))->name('api.v1.health');
    Route::get('/categories', [PublicController::class, 'categories'])->name('api.v1.categories.index');
    Route::get('/services', [PublicController::class, 'services'])->name('api.v1.services.index');
    Route::get('/services/{service}', [PublicController::class, 'service'])->name('api.v1.services.show');
    Route::get('/professionals/{professional}', [PublicController::class, 'professional'])->name('api.v1.professionals.show');
    Route::get('/professionals/{professional}/reviews', [PublicController::class, 'reviews'])->name('api.v1.professionals.reviews');

    Route::prefix('auth')->group(function (): void {
        Route::get('/registration-requirements', [AuthController::class, 'registrationRequirements'])->middleware('throttle:api-read');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:api-register');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');
        Route::post('/google', [AuthController::class, 'google'])->middleware('throttle:api-login');
    });

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::prefix('admin')->middleware('role:admin')->group(function (): void {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->middleware('throttle:admin-actions');
            Route::get('/professionals', [AdminController::class, 'professionals']);
            Route::patch('/professionals/{professional}/verification', [AdminController::class, 'updateVerification'])->middleware('throttle:admin-actions');
            Route::get('/operations/{section}', [AdminOperationsController::class, 'index']);
            Route::post('/categories', [AdminOperationsController::class, 'storeCategory'])->middleware('throttle:admin-actions');
            Route::put('/categories/{category}', [AdminOperationsController::class, 'updateCategory'])->middleware('throttle:admin-actions');
            Route::patch('/categories/{category}/toggle', [AdminOperationsController::class, 'toggleCategory'])->middleware('throttle:admin-actions');
            Route::patch('/services/{service}/moderate', [AdminOperationsController::class, 'moderateService'])->middleware('throttle:admin-actions');
            Route::patch('/reports/{report}/status', [AdminOperationsController::class, 'reportStatus'])->middleware('throttle:admin-actions');
            Route::patch('/reviews/{review}/moderate', [AdminOperationsController::class, 'moderateReview'])->middleware('throttle:admin-actions');
            Route::patch('/disputes/{dispute}/status', [AdminOperationsController::class, 'disputeStatus'])->middleware('throttle:admin-actions');
        });

        Route::middleware('role:client')->group(function (): void {
            Route::get('/favorites', [FavoriteController::class, 'index']);
            Route::post('/favorites/{professional}', [FavoriteController::class, 'store']);
            Route::delete('/favorites/{professional}', [FavoriteController::class, 'destroy']);
            Route::get('/jobs', [JobController::class, 'index']);
            Route::post('/jobs/immediate', [JobController::class, 'immediate'])->middleware('throttle:api-jobs');
            Route::post('/jobs/scheduled', [JobController::class, 'scheduled'])->middleware('throttle:api-jobs');
            Route::post('/jobs/{job}/dispute', [JobController::class, 'dispute'])->middleware('throttle:api-workflow');
        });

        Route::prefix('professional')->middleware('role:professional')->group(function (): void {
            Route::get('/identity-verification', [ProfessionalIdentityVerificationController::class, 'show']);
            Route::post('/identity-verification/start', [ProfessionalIdentityVerificationController::class, 'start'])->middleware('throttle:identity-verification-start');
            Route::post('/identity-verification/sync', [ProfessionalIdentityVerificationController::class, 'sync'])->middleware('throttle:identity-verification-sync');
            Route::get('/profile', [ProfessionalController::class, 'profile']);
            Route::patch('/profile', [ProfessionalController::class, 'updateProfile']);
            Route::get('/jobs', [ProfessionalController::class, 'jobs']);
            Route::get('/services', [ProfessionalController::class, 'services']);
            Route::post('/services', [ProfessionalController::class, 'storeService']);
            Route::get('/services/{service}', [ProfessionalController::class, 'showService']);
            Route::patch('/services/{service}', [ProfessionalController::class, 'updateService']);
            Route::delete('/services/{service}', [ProfessionalController::class, 'destroyService']);
            Route::get('/availability', [ProfessionalController::class, 'availability']);
            Route::put('/availability', [ProfessionalController::class, 'updateAvailability'])->middleware('throttle:api-workflow');
            Route::get('/job-invitations', [ProfessionalController::class, 'invitations']);
            Route::post('/job-invitations/{invitation}/accept', [ProfessionalController::class, 'acceptInvitation'])->middleware('throttle:api-accept');
            Route::post('/job-invitations/{invitation}/decline', [ProfessionalController::class, 'declineInvitation'])->middleware('throttle:api-accept');
            Route::post('/jobs/{job}/quotes', [ProfessionalController::class, 'createQuote'])->middleware('throttle:quotes');
        });

        Route::get('/jobs/{job}/status', [JobController::class, 'status'])->middleware('throttle:api-polling');
        Route::get('/jobs/{job}/quotes', [JobController::class, 'quotes']);
        Route::post('/jobs/{job}/quotes/{quote}/accept', [JobController::class, 'acceptQuote'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/quotes/{quote}/reject', [JobController::class, 'rejectQuote'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/checkout', [JobController::class, 'checkout'])->middleware('throttle:payments');
        Route::post('/jobs/{job}/on-the-way', [JobController::class, 'onTheWay'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/arrived', [JobController::class, 'arrived'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/start', [JobController::class, 'start'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/finish', [JobController::class, 'finish'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/confirm', [JobController::class, 'confirm'])->middleware('throttle:api-workflow');
        Route::post('/jobs/{job}/review', [JobController::class, 'review'])->middleware('throttle:reviews');
        Route::get('/jobs/{job}', [JobController::class, 'show']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read']);
    });
});
