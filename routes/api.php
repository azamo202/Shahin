<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthAdmin\ProfileAdminController;
use App\Http\Controllers\Admin\AuthAdmin\AuthAdminController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\AuthUser\PasswordController;
use App\Http\Controllers\User\AuthUser\AccountController;
use App\Http\Controllers\User\AuthUser\AuthController;
use App\Http\Controllers\User\AuthUser\ProfileController;
use App\Http\Controllers\User\AuthUser\RegisterController;
use App\Http\Controllers\Admin\AdminUserController\AdminUserController;
use App\Http\Controllers\Admin\AdminUserController\AdminUserReportController;
use App\Http\Controllers\Admin\FeaturedClientController\FeaturedClientController;
use App\Http\Controllers\Admin\Landlistings\AdminPropertyController;
use App\Http\Controllers\Admin\Landlistings\AdminPropertyStatusController;
use App\Http\Controllers\User\Auctions\AuctionController;
use App\Http\Controllers\Admin\Auctions\AdminAuctionController;
use App\Http\Controllers\Admin\Auctions\AdminAuctionReportController;
use App\Http\Controllers\Admin\Interested\AdminInterestController;
use App\Http\Controllers\Admin\interested\AdminInterestReportController;
use App\Http\Controllers\User\Auctions\PublicAuctionController;
use App\Http\Controllers\User\Auth\ForgotPasswordController;
use App\Http\Controllers\User\Auth\ResetPasswordController;
use App\Http\Controllers\User\Auth\VerificationController;
use App\Http\Controllers\User\Interested\InterestedController;
use App\Http\Controllers\User\Landlistings\PropertyController;
use App\Http\Controllers\User\Landlistings\PublicPropertyController;
use App\Http\Controllers\User\LandRequest\LandRequestController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Support\Facades\Mail;



Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/home?verified=1');
})->middleware(['signed'])->name('verification.verify');


Route::get('/test-sendgrid', function () {
    try {
        Mail::raw('رسالة اختبار عبر SendGrid SMTP', function ($message) {
            $message->to('azoz20113040@gmail.com') // البريد المستلم
                ->subject('اختبار SendGrid');
        });
        return response()->json(['status' => 'success', 'message' => 'تم الإرسال عبر SendGrid']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});


// تحقق من البريد
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify')
    ->middleware('signed');

// إعادة إرسال رابط التحقق
Route::post('email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// تسجيل مستخدم جديد
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', UserMiddleware::class])->group(function () {
    Route::post('/user/change-password', [PasswordController::class, 'change']);
    Route::delete('/user/delete-account', [AccountController::class, 'destroy']);
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [ProfileController::class, 'profile']);
});


// Routes عامة للأدمن (بدون توكن)
Route::prefix('admin')->group(function () {
    Route::post('/register', [AuthAdminController::class, 'register']); // تسجيل مدير جديد
    Route::post('/login', [AuthAdminController::class, 'login']);       // تسجيل الدخول
});

// إدارة المستخدمين
Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/approved', [AdminUserController::class, 'approved']);
    Route::get('/users/pending', [AdminUserController::class, 'pending']);
    Route::get('/users/rejected', [AdminUserController::class, 'pending']);
    Route::post('/users/{id}/approve', [AdminUserController::class, 'approve']);
    Route::post('/users/{id}/reject', [AdminUserController::class, 'reject']);
    Route::post('/logout', [AuthAdminController::class, 'logout']);
    Route::post('/change-password', [AuthAdminController::class, 'changePassword']);
    Route::get('/profile', [ProfileAdminController::class, 'profile']);
    Route::put('/profile', [ProfileAdminController::class, 'updateProfile']);
    Route::delete('/delete-account', [ProfileAdminController::class, 'deleteAccount']);
    Route::get('/users/report', [AdminUserReportController::class, 'report'])
        ->name('admin.users.report');
    Route::post('/users/report/export', [AdminUserReportController::class, 'exportReport'])
        ->name('admin.users.report.export');
    Route::get('reports', [AdminReportsController::class, 'index']);
    Route::get('dashboard/statistics', [AdminDashboardController::class, 'statistics']);
});


//إدراة الأراضي
//إدراة الأراضي للأدمن
Route::prefix('admin/properties')->middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    Route::get('/', [AdminPropertyController::class, 'getAllProperties']);
    Route::get('/accepted', [AdminPropertyController::class, 'getAcceptedProperties']);
    Route::get('/rejected', [AdminPropertyController::class, 'getRejectedProperties']);
    Route::get('/pending', [AdminPropertyController::class, 'getPendingProperties']);
    Route::get('/sold', [AdminPropertyController::class, 'getSoldProperties']);
    Route::get('/stats', [AdminPropertyController::class, 'getPropertiesStats']);

    // حالات العقار
    Route::put('/{id}/approve', [AdminPropertyStatusController::class, 'approveProperty']);
    Route::put('/{id}/reject', [AdminPropertyStatusController::class, 'rejectProperty']);
    Route::put('/{id}/mark-sold', [AdminPropertyStatusController::class, 'markAsSold']);
    Route::put('/{id}/return-pending', [AdminPropertyStatusController::class, 'returnToPending']);
    Route::put('/{id}/change-status', [AdminPropertyStatusController::class, 'changePropertyStatus']);

    Route::get('/{id}', [AdminPropertyController::class, 'getProperty']);
});


Route::prefix('admin/clients')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/', [FeaturedClientController::class, 'store']);
        Route::put('/{id}', [FeaturedClientController::class, 'update']);
        Route::delete('/{id}', [FeaturedClientController::class, 'destroy']);
    });
Route::prefix('clients')->group(function () {
    Route::get('/Featured', [FeaturedClientController::class, 'index']);
});

//إدارة المزادات
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    Route::get('auction/report', [AdminAuctionReportController::class, 'report']);
    Route::get('auctions', [AdminAuctionController::class, 'index']);
    Route::get('auctions/statistics', [AdminAuctionController::class, 'statistics']);
    Route::get('auctions/{id}', [AdminAuctionController::class, 'show']);
    Route::get('auctions/pending/list', [AdminAuctionController::class, 'pending']);
    Route::post('auctions/{id}/approve', [AdminAuctionController::class, 'approve']);
    Route::post('auctions/{id}/reject', [AdminAuctionController::class, 'reject']);
    Route::delete('auctions/{id}', [AdminAuctionController::class, 'destroy']);
    Route::post('auctions/{id}/change-status', [AdminAuctionController::class, 'changeStatus']);
    Route::get('auctions/statistics', [AdminAuctionController::class, 'statistics']);
});

// إدارة طلبات الاهتمام
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('interests/report', [AdminInterestReportController::class, 'report']);
    Route::prefix('/interests')->group(function () {
        Route::get('/', [AdminInterestController::class, 'index']);
        Route::get('/statistics', [AdminInterestController::class, 'getStatistics']);
        Route::get('/{id}', [AdminInterestController::class, 'show']);
        Route::put('/{id}/status', [AdminInterestController::class, 'updateStatus']);
        Route::delete('/{id}', [AdminInterestController::class, 'destroy']);
    });
});


//User

//الأراضي
// Routes عامة للعقارات (للمسجلين وغير المسجلين)
// ✅ Public Routes (No Auth)
Route::prefix('properties')->group(function () {
    Route::get('/auctions/latest', [PublicAuctionController::class, 'latest']);
    Route::get('/properties/latest', [PublicPropertyController::class, 'latestProperties']);
    Route::get('/', [PublicPropertyController::class, 'index']);
    Route::get('/{id}', [PublicPropertyController::class, 'show']);
    Route::get('/filter-options', [PublicPropertyController::class, 'getFilterOptions']);
});

// ✅ User Routes (Requires Auth)
Route::middleware('auth:sanctum')->prefix('user/properties')->group(function () {
    Route::post('/', [PropertyController::class, 'store']);
    Route::put('/{id}', [PropertyController::class, 'update']);
    Route::patch('/{id}', [PropertyController::class, 'update']);
    Route::delete('/{id}', [PropertyController::class, 'destroy']);
    Route::get('/my', [PropertyController::class, 'myProperties']);
    Route::get('/status/{status}', [PropertyController::class, 'getByStatus']);
    Route::get('/stats', [PropertyController::class, 'getStats']);
});

// تسجيل اهتمام جديد بعقار
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user/interested', [InterestedController::class, 'store'])
        ->name('interested.store');
    Route::get('user/interests/my', [InterestedController::class, 'myInterests'])
        ->name('interested.my');
});


// روابط المزادات العامة
Route::get('/auctions', [PublicAuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/search', [PublicAuctionController::class, 'search'])->name('auctions.search');
Route::get('/auctions/{id}', [PublicAuctionController::class, 'show'])->name('auctions.show');
//المزادات الخاصة بشركات المزاد فقط
Route::middleware('auth:sanctum')->prefix('user/auctions')->group(function () {
    Route::get('/', [AuctionController::class, 'index']);
    Route::get('/stats', [AuctionController::class, 'getStats']);
    Route::get('/{id}', [AuctionController::class, 'show']);
    Route::post('/', [AuctionController::class, 'store']);
    Route::put('/{id}', [AuctionController::class, 'update']);
    Route::delete('/{id}', [AuctionController::class, 'destroy']);
    Route::get('/status/{status}', [AuctionController::class, 'getByStatus']);
});


// 🔐 هذه المسارات تحتاج تسجيل دخول (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // إنشاء طلب جديد
    Route::post('/land-requests', [LandRequestController::class, 'store']);

    // جلب الطلبات الخاصة بالمستخدم الحالي
    Route::get('/land-requests/my', [LandRequestController::class, 'myRequests']);
    Route::put('/land-requests/{id}', [LandRequestController::class, 'update']);
});

// 🌍 هذه المسارات عامة (متاحة للجميع)
Route::get('/land-requests', [LandRequestController::class, 'allRequests']);
Route::get('/land-requests/{id}', [LandRequestController::class, 'show']);


