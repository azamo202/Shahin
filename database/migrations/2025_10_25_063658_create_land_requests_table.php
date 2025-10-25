<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_land_requests_table.php
    public function up(): void
    {
        Schema::create('land_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('region');
            $table->string('city');
            $table->enum('purpose', ['sale', 'investment']);
            $table->enum('type', ['residential', 'commercial', 'agricultural']);
            $table->float('area');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'close', 'completed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_requests');
    }
};
