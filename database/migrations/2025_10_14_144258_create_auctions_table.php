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
        // جدول المزادات الأساسي
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();

            // ربط المزاد بالمستخدم
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('title');                     // عنوان المزاد
            $table->text('description')->nullable();    // وصف المزاد
            $table->string('intro_link')->nullable();   // رابط تعريفي
            $table->time('start_time');                 // وقت بداية المزاد
            $table->date('auction_date');               // تاريخ المزاد
            $table->string('address');                  // الموقع الفيزيائي
            $table->decimal('latitude', 10, 7)->nullable();   // إحداثيات
            $table->decimal('longitude', 10, 7)->nullable();  // إحداثيات
            $table->enum('status', ['قيد المراجعة', 'مقبول', 'مرفوض'])->default('قيد المراجعة'); // حالة المزاد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
