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
        Schema::create('profit', function (Blueprint $table) {
            $table->id();
            $table->double('bank_dice', 16, 2)->default(0.00);
            $table->double('bank_mines', 16, 2)->default(0.00);
            $table->double('bank_bubbles', 16, 2)->default(0.00);
            $table->double('bank_wheel', 16, 2)->default(0.00);
            $table->float('bank_plinko');
            $table->double('earn_bubbles', 16, 2)->default(0.00);
            $table->integer('comission')->default(0);
            $table->double('earn_dice', 16, 2)->default(0.00);
            $table->double('earn_mines', 16, 2)->default(0.00);
            $table->float('earn_plinko');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Индексы
            $table->index(
                ['bank_dice', 'bank_mines', 'bank_bubbles', 'bank_wheel', 'bank_plinko'],
                'profit_bank'
            );

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
        Schema::dropIfExists('profit');
    }
};