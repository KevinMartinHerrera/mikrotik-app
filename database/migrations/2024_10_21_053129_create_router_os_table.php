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
        Schema::create('router_os', function (Blueprint $table) {
            $table->id();
            $table->string('identity');
            $table->string('ip_address');
            $table->string('login');
            $table->string('password')->nullable();
            $table->boolean('connect');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_os');
    }
};
