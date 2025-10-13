<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // ربط المستخدم
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            // هذا يربط الجدول بجدول users، وإذا تم حذف المستخدم يتم حذف عروضه تلقائيًا

            // بيانات أساسية
            $table->string('announcement_number'); // رقم الإعلان
            $table->string('region');               // المنطقة
            $table->string('city');                 // المدينة
            $table->string('title');                // عنوان الأرض
            $table->enum('land_type', ['سكني','تجاري','زراعي']); // نوع الأرض
            $table->enum('purpose', ['بيع','استثمار']);          // الغرض

            // الموقع
            $table->string('geo_location_text');     
            $table->string('geo_location_map')->nullable(); 

            // تفاصيل الأرض
            $table->decimal('total_area', 10, 2);    
            $table->decimal('length_north', 10, 2);
            $table->decimal('length_south', 10, 2);
            $table->decimal('length_east', 10, 2);
            $table->decimal('length_west', 10, 2);
            $table->text('description');             
            $table->string('deed_number');           

            // الصور
            $table->json('images');                  

            // الأسعار والاستثمار
            $table->decimal('price_per_sqm', 15, 2)->nullable();             
            $table->integer('investment_duration')->nullable();               
            $table->decimal('estimated_investment_value', 15, 2)->nullable(); 

            // الوكالة والتعهد
            $table->string('agency_number')->nullable();   
            $table->text('legal_declaration');            

            // حالة العرض
            $table->enum('status', ['قيد المراجعة','مقبول','مرفوض','تم البيع'])->default('قيد المراجعة');

            // تواريخ الإنشاء والتحديث
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
