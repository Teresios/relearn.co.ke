<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http; // <-- Needed for forwarding
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AffiliateApplicationController;
use App\Http\Controllers\Admin\AdminAffiliateApplicationController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\MasterClassController;
use App\Http\Controllers\MasterClassRegistrationController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AdminAffiliateBonusController;
use App\Http\Controllers\AdminAffiliateWithdrawalController;
use App\Http\Controllers\Admin\AdminMasterClassController;
use App\Http\Controllers\DashboardNotificationController;
use App\Http\Controllers\AffiliatePayNowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Quick test route
Route::get('/test', fn () => 'Laravel is working!');

// Public static and main routes
Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('home');
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [HomeController::class, 'show'])->name('products.show');
Route::view('/terms', 'terms')->name('terms');
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Master Classes Routes
Route::get('/master-classes', [MasterClassController::class, 'index'])->name('master-classes.index');
Route::get('/master-classes/{id}', [MasterClassController::class, 'show'])->name('master-classes.show');
Route::get('master-classes/{id}/payment-success', [MasterClassRegistrationController::class, 'paymentSuccess'])->name('master-classes.payment-success');
Route::post('/master-classes/{id}/register', [MasterClassRegistrationController::class, 'register'])->name('master-classes.register');
Route::get('/master-classes/{id}/checkout', [MasterClassRegistrationController::class, 'showCheckout'])->name('master-classes.checkout');
Route::post('/master-classes/{id}/checkout', [MasterClassRegistrationController::class, 'postCheckout'])->name('master-classes.checkout.post');
Route::get('/master-classes/{id}/payment-status', [MasterClassRegistrationController::class, 'paymentStatus'])->name('master-classes.payment-status');
Route::get('/master-classes/{id}/success', [MasterClassRegistrationController::class, 'success'])->name('master-classes.success');

// AI Masterclass Description Generation Route
Route::post('/ai/generate/masterclass-description', [AIController::class, 'generateMasterclassDescription'])
    ->name('ai.generate.masterclass.description');

// Order checkout
Route::get('/orders/checkout/{product}', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('/orders/checkout/{product}', [OrderController::class, 'store'])->name('orders.store');

// Public order status and download (allow guest sessions and token-based downloads)
Route::get('/orders/{order}/payment-status', [OrderController::class, 'paymentStatus'])->name('orders.payment.status');
Route::get('/orders/{order}/check-status', [OrderController::class, 'checkPaymentStatus'])->name('orders.check.status');
Route::get('/orders/{order}/download', [OrderController::class, 'download'])->name('orders.download');
Route::get('/orders/{order}/download-guest', [OrderController::class, 'downloadGuest'])->name('orders.download-guest');

// Mpesa callbacks (CSRF-exempt)
Route::post('/mpesa/stk-callback', [MpesaController::class, 'stkCallback'])->name('mpesa.stk.callback');
Route::post('/mpesa/timeout', [MpesaController::class, 'timeoutCallback'])->name('mpesa.timeout');

// Mpesa Mentorship Callback Forwarding Route (NEW)
Route::post('/mpesa/callback/mentorship/{id}', function($id, \Illuminate\Http\Request $request) {
    $mentorshipUrl = "https://mentorship.relearn.co.ke/api/mpesa/callback/mentorship/{$id}";
    $response = Http::post($mentorshipUrl, $request->all());

    \Log::info('Forwarded M-Pesa mentorship callback', [
        'mentorship_url' => $mentorshipUrl,
        'payload' => $request->all(),
        'forwarded_status' => $response->status(),
        'forwarded_body' => $response->body(),
    ]);

    return response()->json([
        'forwarded' => true,
        'mentorship_status' => $response->status(),
        'mentorship_response' => $response->json(),
    ]);
});

// Test download route
Route::get('/test-download/{token}', fn ($token) => "Download Controller reachable, token: $token");

// Public download routes (guest-accessible with token verification)
Route::prefix('downloads')->name('downloads.')->group(function () {
    Route::get('/select/{token}', [DownloadController::class, 'showFormatSelection'])->name('select-format');
    Route::get('/file/{token}', [DownloadController::class, 'download'])->name('file');
    Route::get('/serve/{token}/{format}', [DownloadController::class, 'serveFile'])->name('serve-file');
});

// Public affiliate application routes (guest-accessible, explicitly not requiring auth)
Route::middleware('web')->group(function () {
    Route::get('/affiliate/apply', [AffiliateApplicationController::class, 'create'])->name('affiliate.apply');
    Route::post('/affiliate/apply', [AffiliateApplicationController::class, 'store'])->name('affiliate.apply.submit');
    Route::get('/affiliate/status/guest', [AffiliateApplicationController::class, 'statusGuest'])->name('affiliate.status.guest');
    Route::get('/affiliate/setup/{token}', [AffiliateApplicationController::class, 'setupShow'])->name('affiliate.setup.show');
    Route::post('/affiliate/setup/{token}', [AffiliateApplicationController::class, 'setupStore'])->name('affiliate.setup.store');
    
    // Referral link tracking (guest-accessible)
    Route::get('/ref/{code}', [ReferralController::class, 'trackAndRedirect'])->name('affiliate.referral.track');
});

// Authentication routes
require __DIR__ . '/auth.php';

// General user dashboard
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        // Checkout and public status/download routes are defined outside
        // this auth-group to allow guest access. Keep only user-only routes here.
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });

    // Downloads - authenticated only
    Route::prefix('downloads')->name('downloads.')->group(function () {
        Route::get('/', [DownloadController::class, 'index'])->name('index');
        Route::post('/generate-link', [DownloadController::class, 'generateLink'])->name('generate');
    });

    // Affiliate Application Status (requires auth or email verification)
    Route::get('/affiliate/status', [AffiliateApplicationController::class, 'status'])->name('affiliate.status');
    Route::get('/status', [AffiliateApplicationController::class, 'status'])->name('affiliate.status.short');

    // Affiliate Dashboard & Features
    Route::get('/affiliate/dashboard', [AffiliateController::class, 'dashboard'])->name('affiliate.dashboard');
    Route::post('/affiliate/link/create', [AffiliateController::class, 'createLink'])->name('affiliate.link.create');
    Route::delete('/affiliate/link/{id}', [AffiliateController::class, 'deleteLink'])->name('affiliate.link.delete');
    Route::get('/affiliate/how-it-works', [AffiliateController::class, 'howItWorks'])->name('affiliate.how_it_works');

    // Withdraw / withdrawal flow (OTP + confirm -> withdraw request)
    Route::post('/affiliate/withdraw/send-otp', [AffiliateController::class, 'sendWithdrawOtp'])->name('affiliate.withdraw.send_otp');
    Route::post('/affiliate/withdraw/verify-otp', [AffiliateController::class, 'verifyWithdrawOtp'])->name('affiliate.withdraw.verify_otp');
    Route::post('/affiliate/withdraw', [AffiliateController::class, 'withdrawRequest'])->name('affiliate.withdraw.request');
});

// Product Admin routes (limited to product management only)
Route::middleware(['auth', 'role:product_admin'])->prefix('products-admin')->name('products-admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('products-admin.dashboard'));
    Route::get('/dashboard', [AdminProductController::class, 'productAdminDashboard'])->name('dashboard');
    
    // Products resource with custom names
    Route::get('/products', [AdminProductController::class, 'index'])->name('index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('store');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('show');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('update');
    Route::patch('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/products/ai-generate-description', [AdminProductController::class, 'aiGenerateDescription'])->name('ai-generate-description');
});

// Admin routes
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product management
    Route::resource('products', AdminProductController::class);
    Route::patch('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('/products/{product}/reset-downloads', [AdminProductController::class, 'resetDownloadLimits'])->name('products.reset-downloads');
    Route::post('/products/{product}/reset-downloads/{user}', [AdminProductController::class, 'resetUserDownloadLimit'])->name('products.reset-downloads-user');
    Route::post('/products/ai-generate-description', [AdminProductController::class, 'aiGenerateDescription'])->name('products.ai-generate-description');

    // Order management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::patch('/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
    });

    // User management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::patch('/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('toggle-role');
        Route::post('/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('assign-role');
        Route::post('/bulk-email', [AdminUserController::class, 'bulkEmail'])->name('bulk-email');
    });

    // Affiliate Application Admin Review
    Route::prefix('affiliate/applications')->name('affiliate.applications.')->group(function () {
        Route::get('/', [AdminAffiliateApplicationController::class, 'index'])->name('index');
        Route::post('/send-weekly-stats', [AdminAffiliateApplicationController::class, 'sendWeeklyStatsEmails'])->name('send-weekly-stats');
        Route::get('/export', [AdminAffiliateApplicationController::class, 'exportCsv'])->name('export');
        Route::get('/{application}', [AdminAffiliateApplicationController::class, 'show'])->name('show');
        Route::patch('/{application}/approve', [AdminAffiliateApplicationController::class, 'approve'])->name('approve');
        Route::patch('/{application}/reject', [AdminAffiliateApplicationController::class, 'reject'])->name('reject');
    });

    // Admin affiliate bonus form and submission
    Route::get('/affiliate-bonus', [AdminAffiliateBonusController::class, 'showForm'])->name('affiliate-bonus.form');
    Route::post('/affiliate-bonus', [AdminAffiliateBonusController::class, 'store'])->name('affiliate-bonus.store');

    // Pay Now button to process affiliate payment
    Route::post('/affiliate/pay-now/{affiliate}', [AffiliatePayNowController::class, 'processPayment'])->name('affiliate.pay-now');
    Route::get('/affiliate/available-earnings/{affiliate}', [AffiliatePayNowController::class, 'getAvailableEarnings'])->name('affiliate.available-earnings');

    // Admin affiliate withdrawal requests
    Route::prefix('affiliate')->name('affiliate.')->group(function () {
        Route::get('/withdrawals', [AdminAffiliateWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::patch('/withdrawals/{id}/approve', [AdminAffiliateWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::patch('/withdrawals/{id}/reject', [AdminAffiliateWithdrawalController::class, 'reject'])->name('withdrawals.reject');
    });

    // Masterclass management
    Route::resource('masterclasses', AdminMasterClassController::class);
    Route::post('/masterclasses/{masterclass}/bulk-email', [AdminMasterClassController::class, 'bulkEmail'])
        ->name('masterclasses.bulk-email');

    // M-Pesa Admin Portal
    Route::prefix('mpesa')->name('mpesa.')->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\MpesaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/realtime', [\App\Http\Controllers\Admin\MpesaDashboardController::class, 'realTimeData'])->name('dashboard.realtime');

        // Transactions
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'export'])->name('export');
            Route::get('/{transaction}', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'show'])->name('show');
            Route::post('/b2c', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'initiateB2C'])->name('b2c');
            Route::post('/b2b', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'initiateB2B'])->name('b2b');
            Route::post('/{transaction}/reverse', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'reverseTransaction'])->name('reverse');
            Route::get('/{transaction}/status', [\App\Http\Controllers\Admin\MpesaTransactionController::class, 'checkStatus'])->name('status');
        });

        // Budgets
        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'store'])->name('store');
            Route::get('/{budget}', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'show'])->name('show');
            Route::get('/{budget}/edit', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'edit'])->name('edit');
            Route::put('/{budget}', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'update'])->name('update');
            Route::delete('/{budget}', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'destroy'])->name('destroy');
            Route::patch('/{budget}/toggle', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'toggle'])->name('toggle');
            Route::post('/{budget}/reset', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'reset'])->name('reset');
            Route::post('/{budget}/recalculate', [\App\Http\Controllers\Admin\MpesaBudgetController::class, 'recalculate'])->name('recalculate');
        });

        // Forwarding Rules
        Route::prefix('forwarding')->name('forwarding.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'store'])->name('store');
            Route::get('/{forwarding}', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'show'])->name('show');
            Route::get('/{forwarding}/edit', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'edit'])->name('edit');
            Route::put('/{forwarding}', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'update'])->name('update');
            Route::delete('/{forwarding}', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'destroy'])->name('destroy');
            Route::patch('/{forwarding}/toggle', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'toggle'])->name('toggle');
            Route::post('/{forwarding}/test', [\App\Http\Controllers\Admin\MpesaForwardingController::class, 'testForward'])->name('test');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MpesaReportController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Admin\MpesaReportController::class, 'generate'])->name('generate');
            Route::get('/daily', [\App\Http\Controllers\Admin\MpesaReportController::class, 'daily'])->name('daily');
            Route::get('/weekly', [\App\Http\Controllers\Admin\MpesaReportController::class, 'weekly'])->name('weekly');
            Route::get('/monthly', [\App\Http\Controllers\Admin\MpesaReportController::class, 'monthly'])->name('monthly');
            Route::get('/custom', [\App\Http\Controllers\Admin\MpesaReportController::class, 'custom'])->name('custom');
            Route::get('/analytics', [\App\Http\Controllers\Admin\MpesaReportController::class, 'analytics'])->name('analytics');
            Route::get('/{report}', [\App\Http\Controllers\Admin\MpesaReportController::class, 'show'])->name('show');
            Route::get('/{report}/pdf', [\App\Http\Controllers\Admin\MpesaReportController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/{report}/csv', [\App\Http\Controllers\Admin\MpesaReportController::class, 'exportCsv'])->name('export.csv');
            Route::delete('/{report}', [\App\Http\Controllers\Admin\MpesaReportController::class, 'destroy'])->name('destroy');
        });
    });
});

// Admin-only notification polling route for dashboard numbers
Route::middleware(['auth', 'role:admin|super_admin'])
    ->get('/dashboard/notifications', [DashboardNotificationController::class, 'check'])
    ->name('dashboard.notifications');

Route::post('/dashboard/notifications/clear', [DashboardNotificationController::class, 'clear'])
    ->name('dashboard.notifications.clear');

// Include M-Pesa test routes (only in development)
if (app()->environment(['local', 'testing'])) {
    require __DIR__ . '/mpesa-test.php';
}