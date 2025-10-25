<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تعديل العمود status لإضافة قيمة 'pending'
        DB::statement("ALTER TABLE land_requests MODIFY status ENUM('open', 'close', 'completed', 'pending') NOT NULL DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // الرجوع إلى الحالة القديمة
        DB::statement("ALTER TABLE land_requests MODIFY status ENUM('open', 'close', 'completed') NOT NULL DEFAULT 'open'");
    }
};
