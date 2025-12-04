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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('sum', 255, 2);
            $table->double('bonus', 16, 2)->default(0.00);
            $table->double('wager', 16, 2)->nullable();
            $table->integer('status')->default(0);
            $table->string('system', 20)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('merchant_meta', 50)->nullable();
            $table->string('method', 16)->nullable();

            // Индексы
            $table->index('created_at');
            $table->index('status');
            $table->index('user_id');

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
        //
    }
};
