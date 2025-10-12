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
        Schema::create('land_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('title', 150); // إلزامي
            $table->enum('land_type', ['زراعي', 'استثماري', 'سكني']); // إلزامي
            $table->string('location', 255); // إلزامي
            $table->decimal('area', 10, 2); // إلزامي

            $table->text('description'); // إلزامي
            $table->string('deed_image', 255); // إلزامي
            $table->enum('purpose', ['بيع', 'استثمار']); // إلزامي

            // السعر التقريبي للمتر (مطلوب إذا الغرض بيع)
            $table->decimal('price_per_meter', 10, 2)->nullable();

            // حقل التواريخ بدل مدة الاستثمار (nullable لتجنب خطأ MySQL عند البيع)
            $table->date('investment_start')->nullable()->comment('تاريخ بدء الاستثمار');
            $table->date('investment_end')->nullable()->comment('تاريخ نهاية الاستثمار');
            $table->decimal('investment_estimated_value', 12, 2)->nullable()->comment('القيمة التقريبية للاستثمار');

            $table->string('real_estate_announcement_no', 100); // إلزامي
            $table->boolean('no_dispute_confirmed')->default(false)->comment('تأكيد المستخدم أن الأرض لا يوجد عليها خلاف');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_listings');
    }
};
