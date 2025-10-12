<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email'); // للتحقق من البريد
            $table->boolean('is_active')->default(true)->after('user_type_id'); // تفعيل الحساب
            $table->rememberToken()->after('is_active'); // لدعم تذكر الجلسات وSanctum
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
            $table->dropColumn('is_active');
            $table->dropColumn('remember_token');
        });
    }
};
