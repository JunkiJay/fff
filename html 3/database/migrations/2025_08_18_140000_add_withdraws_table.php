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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('sum', 255, 2);
            $table->double('sumWithCom', 16, 2)->default(0.00);
            $table->string('wallet', 255);
            $table->string('system', 20);
            $table->string('reason', 500)->nullable();
            $table->integer('status')->default(0);
            $table->integer('fake')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('is_youtuber')->default(0);
            $table->string('method', 16)->nullable();

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
        Schema::dropIfExists('withdraws');
    }
};