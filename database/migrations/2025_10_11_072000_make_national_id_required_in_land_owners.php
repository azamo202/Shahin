<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('land_owners', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('land_owners', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable()->change();
        });
    }
};

