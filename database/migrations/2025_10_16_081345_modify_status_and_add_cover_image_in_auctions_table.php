<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ تعديل جدول المزادات: إضافة cover_image وتغيير enum status
        Schema::table('auctions', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('longitude'); // حقل الغلاف
            $table->enum('status', ['قيد المراجعة', 'مفتوح', 'مرفوض'])
                ->default('قيد المراجعة')
                ->change();
        });

        // 2️⃣ تحديث البيانات القديمة من "مقبول" إلى "مفتوح"
        DB::table('auctions')
            ->where('status', 'مقبول')
            ->update(['status' => 'مفتوح']);
    }

    public function down(): void
    {
        // عكس العملية في حالة rollback
        Schema::table('auctions', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مقبول', 'مرفوض'])
                ->default('قيد المراجعة')
                ->change();
            $table->dropColumn('cover_image'); // إزالة حقل الغلاف
        });

        // إعادة أي سجل من "مفتوح" إلى "مقبول"
        DB::table('auctions')
            ->where('status', 'مفتوح')
            ->update(['status' => 'مقبول']);
    }
};
