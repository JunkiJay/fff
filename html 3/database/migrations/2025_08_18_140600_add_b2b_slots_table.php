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
        Schema::create('b2b_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('show')->default(0);
            $table->string('gr_title', 255);
            $table->integer('gr_id');
            $table->boolean('gm_is_board');
            $table->integer('gm_m_w');
            $table->integer('gm_ln');
            $table->boolean('gm_is_copy');
            $table->string('gm_url', 255);
            $table->boolean('gm_is_retro');
            $table->integer('gm_bk_id');
            $table->integer('gm_d_w');
            $table->string('icon_url', 255);
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
        Schema::dropIfExists('b2b_slots');
    }
};