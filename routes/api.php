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
use App\Http\Controllers\User\Landlistings\PropertyController;
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
//User

//الأراضي
// Routes عامة للعقارات (للمسجلين وغير المسجلين)
Route::prefix('properties')->group(function () {
    // جلب جميع العقارات المقبولة للواجهة العامة
    Route::get('/public', [PropertyController::class, 'indexPublic']);
    Route::get('/public/{id}', [PropertyController::class, 'showPublic']);
});

// Routes للمستخدمين المسجلين (كما عندك)
Route::middleware(['auth:sanctum', UserMiddleware::class ])->group(function () {
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/stats', [PropertyController::class, 'getStats']);
        Route::get('/status/{status}', [PropertyController::class, 'getByStatus']);
        Route::get('/{id}', [PropertyController::class, 'show']);
        Route::put('/{id}', [PropertyController::class, 'update']);
        Route::delete('/{id}', [PropertyController::class, 'destroy']);
    });
});