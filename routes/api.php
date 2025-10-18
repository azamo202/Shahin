<?php

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
use App\Http\Controllers\Admin\FeaturedClientController\FeaturedClientController;
use App\Http\Controllers\Admin\Landlistings\AdminPropertyController;
use App\Http\Controllers\Admin\Landlistings\AdminPropertyStatusController;
use App\Http\Controllers\User\Auctions\AuctionController;
use App\Http\Controllers\User\Auctions\PublicAuctionController;
use App\Http\Controllers\User\Landlistings\PropertyController;
use App\Http\Controllers\User\Landlistings\PublicPropertyController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\UserMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// رابط التحقق من البريد
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'تم التحقق من البريد الإلكتروني بنجاح']);
})->middleware(['auth', 'signed'])->name('verification.verify');

// رابط لإعادة إرسال الإيميل
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'تم إرسال رابط التحقق إلى بريدك']);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

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
    Route::post('/users/{id}/approve', [AdminUserController::class, 'approve']);
    Route::post('/users/{id}/reject', [AdminUserController::class, 'reject']);
    Route::post('/logout', [AuthAdminController::class, 'logout']);
    Route::post('/change-password', [AuthAdminController::class, 'changePassword']);
    Route::get('/profile', [ProfileAdminController::class, 'profile']);
    Route::put('/profile', [ProfileAdminController::class, 'updateProfile']);
    Route::delete('/delete-account', [ProfileAdminController::class, 'deleteAccount']);
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

//User

//الأراضي
// Routes عامة للعقارات (للمسجلين وغير المسجلين)
// ✅ Public Routes (No Auth)
Route::prefix('properties')->group(function () {
    Route::get('/auctions/latest', [PublicAuctionController::class, 'latest']);
    Route::get('/properties/latest', [PropertyController::class, 'latest']);
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
