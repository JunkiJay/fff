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
        Schema::create('promocodes', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 255);
            $table->double('sum', 255, 2);
            $table->integer('activation');
            $table->double('wager', 16, 2)->default(0.00);
            $table->string('type', 50);
            $table->timestamp('end_time')->nullable();
            $table->integer('quantity_spin')->nullable();
            $table->integer('id_spin')->nullable();
            $table->double('min_deposits')->nullable();
            $table->integer('deposits_days')->nullable();
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
        Schema::dropIfExists('promocodes');
    }
};