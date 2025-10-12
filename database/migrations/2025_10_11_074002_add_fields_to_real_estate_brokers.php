<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_brokers', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable(false)->change(); // اجعل العمود إلزامي فقط
            $table->string('license_number', 100)->nullable(false)->change(); // اجعل الترخيص إلزامي
            $table->string('license_file', 255)->after('license_number'); // إضافة جديدة
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_brokers', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable()->change();
            $table->string('license_number', 100)->nullable()->change();
            $table->dropColumn('license_file');
        });
    }
};
