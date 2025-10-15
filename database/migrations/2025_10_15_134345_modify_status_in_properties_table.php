<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ إضافة الحالة الجديدة "مفتوح" مؤقتاً مع القديمة "مقبول"
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مقبول', 'مفتوح', 'مرفوض', 'تم البيع'])
                ->default('قيد المراجعة')
                ->change();
        });

        // 2️⃣ تحديث البيانات من "مقبول" إلى "مفتوح"
        DB::table('properties')
            ->where('status', 'مقبول')
            ->update(['status' => 'مفتوح']);

        // 3️⃣ إزالة "مقبول" من ENUM بعد التحديث
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مفتوح', 'مرفوض', 'تم البيع'])
                ->default('قيد المراجعة')
                ->change();
        });
    }

    public function down(): void
    {
        // عكس العملية في حالة rollback

        // 1️⃣ إضافة "مقبول" مؤقتاً مع "مفتوح"
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مفتوح', 'مقبول', 'مرفوض', 'تم البيع'])
                ->default('قيد المراجعة')
                ->change();
        });

        // 2️⃣ إرجاع "مفتوح" إلى "مقبول"
        DB::table('properties')
            ->where('status', 'مفتوح')
            ->update(['status' => 'مقبول']);

        // 3️⃣ إزالة "مفتوح" من ENUM
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مقبول', 'مرفوض', 'تم البيع'])
                ->default('قيد المراجعة')
                ->change();
        });
    }
};
