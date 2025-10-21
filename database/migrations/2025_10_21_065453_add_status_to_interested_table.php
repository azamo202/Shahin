<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interested', function (Blueprint $table) {
            // إضافة حقل الحالة (موجود عندك)
            $table->enum('status', ['ملغي', 'قيد المراجعة', 'تمت المراجعة', 'تم التواصل'])
                ->default('قيد المراجعة')
                ->after('property_id');

            // إضافة حقل ملاحظات المسؤول
            $table->text('admin_notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('interested', function (Blueprint $table) {
            $table->dropColumn('admin_notes');
            $table->dropColumn('status');
        });
    }
};
