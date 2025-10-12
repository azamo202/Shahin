<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('land_listings', function (Blueprint $table) {
            $table->enum('status', ['قيد المراجعة', 'مقبول', 'مرفوض', 'تم البيع'])
                ->default('قيد المراجعة')
                ->after('no_dispute_confirmed')
                ->comment('حالة عرض الأرض، يتم تعديلها من قبل الإدارة فقط');
        });
    }

    public function down(): void
    {
        Schema::table('land_listings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
