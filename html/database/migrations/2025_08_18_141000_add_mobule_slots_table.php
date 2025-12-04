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
        Schema::create('mobule_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('show')->default(0);
            $table->string('alias', 255);
            $table->string('group_alias', 255);
            $table->string('title', 255);
            $table->string('provider', 255);
            $table->boolean('is_enabled');
            $table->boolean('is_freerounds_enabled');
            $table->boolean('desktop_enabled');
            $table->boolean('mobile_enabled');
            $table->integer('base_total_bet');
            $table->integer('max_bet_level');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Параметры таблицы
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobule_slots');
    }
};