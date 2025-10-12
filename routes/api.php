<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthAdmin\ProfileAdminController;
use App\Http\Controllers\Admin\AuthAdmin\AuthAdminController;
use App\Http\Controllers\User\Landlistings\LandListingController;
use App\Http\Controllers\User\Landlistings\UserLandListingController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\AuthUser\PasswordController;
use App\Http\Controllers\User\AuthUser\AccountController;
use App\Http\Controllers\User\AuthUser\AuthController;
use App\Http\Controllers\User\AuthUser\ProfileController;
use App\Http\Controllers\User\AuthUser\RegisterController;
use App\Http\Controllers\Admin\AdminUserController\AdminUserController;
use App\Http\Middleware\IsAdmin;

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
Route::middleware('auth:sanctum')->group(function () {
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

Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
    // إدارة المستخدمين
    Route::get('/users', [AdminUserController::class, 'index']); 
    Route::get('/users/approved', [AdminUserController::class, 'approved']); 
    Route::get('/users/pending', [AdminUserController::class, 'pending']); 
    Route::post('/users/{id}/approve', [AdminUserController::class, 'approve']); 
    Route::post('/users/{id}/reject', [AdminUserController::class, 'reject']); 

    // العمليات السابقة
    Route::post('/logout', [AuthAdminController::class, 'logout']);               
    Route::post('/change-password', [AuthAdminController::class, 'changePassword']); 
    Route::get('/profile', [ProfileAdminController::class, 'profile']);          
    Route::put('/profile', [ProfileAdminController::class, 'updateProfile']);   
    Route::delete('/delete-account', [ProfileAdminController::class, 'deleteAccount']); 
});
//User

//الأراضي
// Routes العامة (لجميع المستخدمين)
Route::prefix('land-listings')->group(function () {
    Route::get('/', [LandListingController::class, 'index']);
    Route::get('/{id}', [LandListingController::class, 'show']);
});

// Routes المحمية (تتطلب توكن)
Route::middleware('auth:sanctum')->prefix('land-listings')->group(function () {
    // إدارة قوائم الأراضي
    Route::post('/', [LandListingController::class, 'store']);
    Route::put('/{id}', [LandListingController::class, 'update']);
    Route::delete('/{id}', [LandListingController::class, 'destroy']);

    // قوائم الأراضي الخاصة بالمستخدم
    Route::prefix('my-listings')->group(function () {
        Route::get('/', [UserLandListingController::class, 'index']);
        Route::get('/statistics', [UserLandListingController::class, 'statistics']);
    });
});
