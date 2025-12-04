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
        Schema::create('slot_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->bigInteger('game_id');
            
            // In Laravel, timestamps() automatically adds created_at and updated_at
            // But we need to customize them to match the SQL schema
            $table->timestamp('created_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Индексы
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_sessions');
    }
};