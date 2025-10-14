<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // 1. حذف عمود الصور القديم (JSON)
            $table->dropColumn('images');

            // 2. إضافة صورة الغلاف بعد الوصف مباشرة
            $table->string('cover_image')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // التراجع: إعادة عمود images وحذف cover_image
            $table->json('images')->nullable();
            $table->dropColumn('cover_image');
        });
    }
};
