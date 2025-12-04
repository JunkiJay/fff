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
        Schema::create('mines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->float('amount');
            $table->integer('bombs');
            $table->integer('step')->default(0);
            $table->json('grid')->nullable();
            $table->integer('status')->default(0);
            $table->integer('fake')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Индексы
            $table->index('status');
            $table->index('user_id');

            // Параметры таблицы
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mines');
    }
};