<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interested', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');         // الاسم الثلاثي (إلزامي)
            $table->string('phone');             // رقم الجوال (إلزامي)
            $table->string('email');             // الايميل (إلزامي)
            $table->text('message');             // رسالة تعبيرية (إلزامي)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); 
            // إذا لديه حساب
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade'); 
            // يربط الأرض المراد شراءها (إلزامي)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interested');
    }
};
