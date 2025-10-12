<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_entities', function (Blueprint $table) {
            $table->string('business_name', 150)->after('user_id');
            $table->string('commercial_register', 100)->change(); // نؤكد أنه not null
            $table->string('national_id', 50)->unique()->after('commercial_register');
            $table->string('commercial_file', 255)->after('national_id');
        });
    }

    public function down(): void
    {
        Schema::table('business_entities', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'national_id', 'commercial_file']);
        });
    }
};

