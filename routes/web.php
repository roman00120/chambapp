<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProfessionalController as AdminProfessionalController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientJobRequestController;
use App\Http\Controllers\ClientOnDemandController;
use App\Http\Controllers\ClientPaymentHistoryController;
use App\Http\Controllers\ClientQuoteController;
use App\Http\Controllers\CommerceController;
use App\Http\Controllers\Dashboard\ClientDashboardController;
use App\Http\Controllers\Dashboard\ProfessionalDashboardController;
use App\Http\Controllers\DiditWebhookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobDisputeController;
use App\Http\Controllers\JobRequestController;
use App\Http\Controllers\JobWorkflowController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Professional\IdentityVerificationTransferController;
use App\Http\Controllers\Professional\ProfessionalIdentityVerificationController;
use App\Http\Controllers\Professional\ProfessionalPaymentController;
use App\Http\Controllers\Professional\ProfessionalProfileController;
use App\Http\Controllers\Professional\ProfessionalServiceController;
use App\Http\Controllers\Professional\ServiceImageController;
use App\Http\Controllers\ProfessionalEarningsController;
use App\Http\Controllers\ProfessionalJobRequestController;
use App\Http\Controllers\ProfessionalOpportunityController;
use App\Http\Controllers\ProfessionalPublicProfileController;
use App\Http\Controllers\ProfessionalQuoteController;
use App\Http\Controllers\PublicServiceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TipController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/health', HealthController::class)->name('health');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/consentimiento', [GoogleAuthController::class, 'consent'])->name('auth.google.consent');
Route::post('/auth/google/registro', [GoogleAuthController::class, 'registrationRedirect'])->name('auth.google.register');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/compras/{state}', [CommerceController::class, 'purchaseReturn'])->whereIn('state', ['success', 'pending', 'error'])->name('commerce.return');
Route::get('/compras/exito', fn () => app(CommerceController::class)->purchaseReturn('success'))->name('commerce.return.success');
Route::get('/compras/pendiente', fn () => app(CommerceController::class)->purchaseReturn('pending'))->name('commerce.return.pending');
Route::get('/compras/error', fn () => app(CommerceController::class)->purchaseReturn('error'))->name('commerce.return.error');
Route::get('/terminos', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacidad', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/cancelaciones', [LegalController::class, 'cancellations'])->name('legal.cancellations');
Route::get('/para-profesionales', [LegalController::class, 'professionals'])->name('legal.professionals');
Route::get('/contacto', [LegalController::class, 'contact'])->name('legal.contact');
Route::view('/verificacion-identidad/regreso', 'professional.identity-verification.mobile-return')
    ->name('identity-verification.mobile-return');
Route::get('/sitemap.xml', fn () => response()->view('seo.sitemap', [], 200, ['Content-Type' => 'application/xml']))->name('sitemap');
Route::get('/buscar', [MarketplaceController::class, 'search'])->name('marketplace.search');
Route::get('/servicios', [MarketplaceController::class, 'search'])->name('marketplace.services');
Route::get('/servicios/{service}', [PublicServiceController::class, 'show'])->name('marketplace.service');
Route::get('/categorias', [CategoryController::class, 'index'])->name('marketplace.categories');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('marketplace.category');
Route::get('/profesionales/{professionalProfile}/reseñas', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/profesionales/{professionalProfile}', ProfessionalPublicProfileController::class)->name('professional.public-profile');
Route::get('/pagos/exito', [PaymentController::class, 'success'])->middleware(['auth', 'active'])->name('payments.return.success');
Route::get('/pagos/pendiente', [PaymentController::class, 'pending'])->middleware(['auth', 'active'])->name('payments.return.pending');
Route::get('/pagos/error', [PaymentController::class, 'error'])->middleware(['auth', 'active'])->name('payments.return.error');
Route::post('/webhooks/mercadopago', MercadoPagoWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('webhooks.mercadopago');
Route::post('/webhooks/didit', DiditWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('webhooks.didit');

Route::post('/modo-activo', [\App\Http\Controllers\ModeSwitchController::class, 'switch'])
    ->middleware(['auth', 'active'])
    ->name('active-mode.switch');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/mi-cuenta/seguridad', [\App\Http\Controllers\UserReportController::class, 'securityCenter'])->name('account.security');
    Route::get('/reportar', [\App\Http\Controllers\UserReportController::class, 'create'])->name('reports.create');
    Route::post('/reportar', [\App\Http\Controllers\UserReportController::class, 'store'])->name('reports.store');
    Route::post('/sanciones/{action}/apelar', [\App\Http\Controllers\UserReportController::class, 'appeal'])->name('disciplinary.appeal');
});

Route::middleware(['auth', 'active', 'role:client'])->group(function () {
    Route::get('/servicios/{service}/solicitar', [ClientJobRequestController::class, 'create'])->name('job-requests.create');
    Route::post('/servicios/{service}/solicitar', [ClientJobRequestController::class, 'store'])->name('job-requests.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store'])->middleware('throttle:register')->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/verificar-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verificar-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/verificar-email/reenviar', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:verification-email')
        ->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/verificacion/continuar/{token}', IdentityVerificationTransferController::class)
    ->middleware(['auth', 'active', 'role:professional', 'throttle:identity-verification-transfer'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('identity-verification.transfer');

Route::middleware(['auth', 'active', 'role:client'])
    ->prefix('cliente')
    ->name('client.')
    ->group(function () {
        Route::get('/inicio', ClientDashboardController::class)->name('dashboard');
        Route::get('/ahora', [ClientOnDemandController::class, 'create'])->name('ondemand.create');
        Route::post('/ahora', [ClientOnDemandController::class, 'store'])->middleware('throttle:workflow')->name('ondemand.store');
        Route::get('/ahora/{jobRequest}', [ClientOnDemandController::class, 'show'])->name('ondemand.show');
        Route::get('/ahora/{jobRequest}/estado', [ClientOnDemandController::class, 'status'])->middleware('throttle:workflow')->name('ondemand.status');
        Route::post('/ahora/{jobRequest}/cancelar', [ClientOnDemandController::class, 'cancel'])->middleware('throttle:workflow')->name('ondemand.cancel');
        Route::post('/ahora/{jobRequest}/buscar-otro', [ClientOnDemandController::class, 'searchAgain'])->middleware('throttle:workflow')->name('ondemand.search-again');
        Route::get('/programar', [ClientOnDemandController::class, 'scheduledCreate'])->name('scheduled.create');
        Route::post('/programar', [ClientOnDemandController::class, 'scheduledStore'])->middleware('throttle:workflow')->name('scheduled.store');
        Route::get('/trabajos', [ClientJobRequestController::class, 'index'])->name('jobs.index');
        Route::get('/favoritos', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::get('/pagos', ClientPaymentHistoryController::class)->name('payments.index');
        Route::get('/pagos/{jobRequest}/resumen', [PaymentController::class, 'summary'])->name('payments.summary');
        Route::post('/pagos/{jobRequest}/checkout', [PaymentController::class, 'checkout'])->middleware('throttle:payments')->name('payments.checkout');
    });

Route::post('/profesionales/{professionalProfile}/favorito', [FavoriteController::class, 'toggle'])
    ->middleware(['auth', 'active', 'role:client'])
    ->name('professional.favorite.toggle');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notificaciones/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notificaciones/marcar-todas', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/trabajos/{jobRequest}', [JobRequestController::class, 'show'])->name('job-requests.show');
    Route::post('/trabajos/{jobRequest}/aceptar', [JobWorkflowController::class, 'acceptDirect'])->middleware('throttle:workflow')->name('job-requests.accept-direct');
    Route::post('/trabajos/{jobRequest}/rechazar', [JobWorkflowController::class, 'reject'])->middleware('throttle:workflow')->name('job-requests.reject');
    Route::post('/trabajos/{jobRequest}/iniciar', [JobWorkflowController::class, 'start'])->middleware('throttle:workflow')->name('job-requests.start');
    Route::post('/trabajos/{jobRequest}/en-camino', [JobWorkflowController::class, 'onTheWay'])->middleware('throttle:workflow')->name('job-requests.on-the-way');
    Route::post('/trabajos/{jobRequest}/llegue', [JobWorkflowController::class, 'arrive'])->middleware('throttle:workflow')->name('job-requests.arrive');
    Route::post('/trabajos/{jobRequest}/terminar', [JobWorkflowController::class, 'finish'])->middleware('throttle:workflow')->name('job-requests.finish');
    Route::post('/trabajos/{jobRequest}/confirmar', [JobWorkflowController::class, 'complete'])->middleware('throttle:workflow')->name('job-requests.complete');
    Route::post('/trabajos/{jobRequest}/propina', [TipController::class, 'store'])->middleware('throttle:payments')->name('job-requests.tip');
    Route::post('/trabajos/{jobRequest}/problema', [JobDisputeController::class, 'store'])->middleware('throttle:workflow')->name('job-requests.dispute');
    Route::post('/trabajos/{jobRequest}/cancelar', [JobWorkflowController::class, 'cancel'])->middleware('throttle:workflow')->name('job-requests.cancel');
    Route::post('/trabajos/{jobRequest}/cotizaciones', [ProfessionalQuoteController::class, 'store'])->middleware('throttle:quotes')->name('job-quotes.store');
    Route::post('/cotizaciones/{jobQuote}/aceptar', [ClientQuoteController::class, 'accept'])->middleware('throttle:workflow')->name('job-quotes.accept');
    Route::post('/cotizaciones/{jobQuote}/rechazar', [ClientQuoteController::class, 'reject'])->middleware('throttle:workflow')->name('job-quotes.reject');
    Route::get('/trabajos/{jobRequest}/calificar', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/trabajos/{jobRequest}/calificar', [ReviewController::class, 'store'])->middleware('throttle:reviews')->name('reviews.store');
    Route::post('/reseñas/{review}/reportar', [ReviewController::class, 'report'])->middleware(['role:professional', 'throttle:reviews'])->name('reviews.report');
});

Route::middleware(['auth', 'active', 'role:professional'])
    ->prefix('profesional')
    ->name('professional.')
    ->group(function () {
        Route::get('/inicio', ProfessionalDashboardController::class)->name('dashboard');
        Route::get('/verificacion-identidad', ProfessionalIdentityVerificationController::class)->name('identity-verification.show');
        Route::post('/verificacion-identidad/iniciar', [ProfessionalIdentityVerificationController::class, 'start'])
            ->middleware('throttle:identity-verification-start')
            ->name('identity-verification.start');
        Route::get('/verificacion-identidad/callback', [ProfessionalIdentityVerificationController::class, 'callback'])
            ->middleware('throttle:identity-verification-sync')
            ->name('identity-verification.callback');
        Route::get('/verificacion-identidad/estado', [ProfessionalIdentityVerificationController::class, 'status'])
            ->middleware('throttle:api-polling')
            ->name('identity-verification.status');
        Route::get('/chambas', [ProfessionalOpportunityController::class, 'index'])->name('opportunities');
        Route::get('/chambas/estado', [ProfessionalOpportunityController::class, 'status'])->middleware('throttle:workflow')->name('opportunities.status');
        Route::post('/chambas/{invitation}/aceptar', [ProfessionalOpportunityController::class, 'accept'])->middleware('throttle:workflow')->name('opportunities.accept');
        Route::post('/chambas/{invitation}/declinar', [ProfessionalOpportunityController::class, 'decline'])->middleware('throttle:workflow')->name('opportunities.decline');
        Route::put('/disponibilidad', [ProfessionalOpportunityController::class, 'availability'])->middleware('throttle:workflow')->name('availability.update');
        Route::post('/ubicacion', [ProfessionalOpportunityController::class, 'location'])->middleware('throttle:workflow')->name('location.update');
        Route::get('/solicitudes', [ProfessionalJobRequestController::class, 'index'])->name('jobs.index');
        Route::get('/perfil', [ProfessionalProfileController::class, 'show'])->name('profile.show');
        Route::get('/perfil/editar', [ProfessionalProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/perfil', [ProfessionalProfileController::class, 'update'])->name('profile.update');
        Route::get('/pagos/configuracion', [ProfessionalPaymentController::class, 'show'])->name('payments.settings');
        Route::get('/pagos/configuracion/conectar', [ProfessionalPaymentController::class, 'connect'])->name('payments.connect');
        Route::get('/pagos/configuracion/oauth/callback', [ProfessionalPaymentController::class, 'callback'])->name('payments.oauth-callback');
        Route::get('/ganancias', ProfessionalEarningsController::class)->name('earnings');
        Route::get('/promocionar', [CommerceController::class, 'featured'])->name('commerce.featured');
        Route::post('/promocionar/{service}', [CommerceController::class, 'buyFeatured'])->name('commerce.featured.buy');
        Route::get('/tienda', [CommerceController::class, 'store'])->name('commerce.store');
        Route::post('/tienda/{item}', [CommerceController::class, 'buyItem'])->name('commerce.store.buy');

        Route::get('/servicios', [ProfessionalServiceController::class, 'index'])->name('services.index');
        Route::get('/servicios/crear', [ProfessionalServiceController::class, 'create'])->name('services.create');
        Route::post('/servicios', [ProfessionalServiceController::class, 'store'])->name('services.store');
        Route::get('/servicios/{service}/editar', [ProfessionalServiceController::class, 'edit'])->name('services.edit');
        Route::put('/servicios/{service}', [ProfessionalServiceController::class, 'update'])->name('services.update');
        Route::patch('/servicios/{service}/activar', [ProfessionalServiceController::class, 'toggle'])->name('services.toggle');
        Route::delete('/servicios/{service}', [ProfessionalServiceController::class, 'destroy'])->name('services.destroy');
        Route::delete('/imagenes/{serviceImage}', [ServiceImageController::class, 'destroy'])->name('service-images.destroy');
    });

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/inicio', AdminDashboardController::class)->name('dashboard');
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('/usuarios/{user}/estado', [AdminUserController::class, 'status'])->middleware('throttle:admin-actions')->name('users.status');
        Route::get('/profesionales', [AdminProfessionalController::class, 'index'])->name('professionals.index');
        Route::get('/profesionales/{professional}', [AdminProfessionalController::class, 'show'])->name('professionals.show');
        Route::post('/profesionales/{professional}/aprobar', [AdminProfessionalController::class, 'approve'])->middleware('throttle:admin-actions')->name('professionals.approve');
        Route::post('/profesionales/{professional}/rechazar', [AdminProfessionalController::class, 'reject'])->middleware('throttle:admin-actions')->name('professionals.reject');
        Route::get('/categorias', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categorias/crear', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categorias', [AdminCategoryController::class, 'store'])->middleware('throttle:admin-actions')->name('categories.store');
        Route::get('/categorias/{category}/editar', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categorias/{category}', [AdminCategoryController::class, 'update'])->middleware('throttle:admin-actions')->name('categories.update');
        Route::patch('/categorias/{category}/estado', [AdminCategoryController::class, 'toggle'])->middleware('throttle:admin-actions')->name('categories.toggle');
        Route::get('/servicios', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/servicios/{service}', [AdminServiceController::class, 'show'])->name('services.show');
        Route::patch('/servicios/{service}/moderacion', [AdminServiceController::class, 'moderate'])->middleware('throttle:admin-actions')->name('services.moderate');
        Route::get('/trabajos', [AdminJobController::class, 'index'])->name('jobs.index');
        Route::get('/trabajos/{job}', [AdminJobController::class, 'show'])->name('jobs.show');
        Route::get('/pagos', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/pagos/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::get('/comisiones', [AdminCommissionController::class, 'index'])->name('commissions.index');
        Route::get('/reportes', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'index'])->name('reports.index');
        Route::get('/reportes/{report}', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'show'])->name('reports.show');
        Route::post('/reportes/{report}/resolver', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'resolve'])->middleware('throttle:admin-actions')->name('reports.resolve');
        Route::get('/apelaciones', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'appeals'])->name('reports.appeals');
        Route::post('/apelaciones/{appeal}/resolver', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'resolveAppeal'])->middleware('throttle:admin-actions')->name('reports.appeals.resolve');
        Route::get('/evidencia/{evidence}/descargar', [\App\Http\Controllers\Admin\DisciplinaryController::class, 'downloadEvidence'])->name('reports.evidence.download');
        Route::get('/resenas', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/resenas/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::patch('/resenas/{review}/moderacion', [AdminReviewController::class, 'moderate'])->middleware('throttle:admin-actions')->name('reviews.moderate');
        Route::get('/disputas', [AdminDisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputas/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
        Route::patch('/disputas/{dispute}/estado', [AdminDisputeController::class, 'status'])->middleware('throttle:admin-actions')->name('disputes.status');
    });
