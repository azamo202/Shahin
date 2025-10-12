<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_companies', function (Blueprint $table) {
            $table->string('auction_name', 150)->after('commercial_register');
            $table->string('national_id', 50)->unique()->after('auction_name');
            $table->string('commercial_file', 255)->after('national_id');
            $table->string('license_number', 100)->after('commercial_file');
            $table->string('license_file', 255)->after('license_number');
        });
    }

    public function down(): void
    {
        Schema::table('auction_companies', function (Blueprint $table) {
            $table->dropColumn(['auction_name', 'national_id', 'commercial_file', 'license_number', 'license_file']);
        });
    }
};
