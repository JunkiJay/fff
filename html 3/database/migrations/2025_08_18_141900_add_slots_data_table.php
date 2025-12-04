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
        Schema::create('slots_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('slot_id');
            $table->bigInteger('trx_id');
            $table->string('type', 55)->nullable();
            $table->integer('amount');
            $table->double('balanceBefore')->nullable();
            $table->double('balanceAfter')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Параметры таблицы
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots_data');
    }
};