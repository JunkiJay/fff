<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('kassa_id', 255)->nullable();
            $table->string('kassa_secret1', 255)->nullable();
            $table->string('kassa_secret2', 255)->nullable();
            $table->string('kassa_key', 50)->nullable();
            $table->string('wallet_id', 500)->nullable();
            $table->string('wallet_secret', 500)->nullable();
            $table->string('wallet_desc', 50)->nullable();
            $table->string('vlito_id', 50)->nullable();
            $table->string('vlito_secret', 50)->nullable();
            $table->double('min_payment_sum', 8, 2)->nullable();
            $table->double('min_bonus_sum', 8, 2)->nullable();
            $table->double('min_withdraw_sum', 8, 2)->nullable();
            $table->integer('min_dep_withdraw')->nullable();
            $table->integer('withdraw_request_limit')->nullable();
            $table->string('vk_url', 255)->nullable();
            $table->string('tg_channel', 250)->nullable();
            $table->string('tg_bot', 250)->nullable();
            $table->string('vk_id', 50)->nullable();
            $table->string('vk_token', 255)->nullable();
            $table->string('vk_service_token', 500)->nullable();
            $table->double('bot_timer', 8, 2)->nullable();
            $table->integer('file_version')->default(1);
            $table->integer('antiminus')->default(0);
            $table->double('daily_bonus_min', 16, 2)->default(0.00);
            $table->double('daily_bonus_max', 16, 2)->default(0.00);
            $table->double('hourly_bonus_min', 16, 2)->default(0.00);
            $table->double('hourly_bonus_max', 16, 2)->default(0.00);
            $table->float('onetime_bonus')->default(0);
            $table->bigInteger('telegram_chat_id')->nullable();
            $table->string('telegram_token', 300)->nullable();
            $table->string('referral_domain', 50)->nullable();
            $table->double('referral_reward', 16, 2)->default(0.00);
            $table->integer('deposit_per_n')->default(0);
            $table->double('deposit_sum_n', 16, 2)->default(0.00);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Кодировка и колlation как в исходной схеме
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::create('setting_tournaments', function (Blueprint $table) {
            $table->id();
            $table->integer('days')->default(7);
            $table->text('places')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Кодировка и сорировка, как в исходной схеме
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
