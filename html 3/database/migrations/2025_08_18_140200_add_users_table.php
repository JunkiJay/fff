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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('unique_id', 16);
            $table->string('username', 256);
            $table->string('email', 255)->nullable();
            $table->double('balance', 255, 2)->default(0.00);
            $table->string('password', 255)->nullable();
            $table->tinyInteger('auto_payment')->default(0);
            $table->string('reset_token', 255)->nullable();
            $table->integer('freespins')->nullable();
            $table->integer('fs_slot')->nullable();
            $table->integer('fs_amount')->nullable();
            $table->text('freeround_id')->nullable();
            $table->double('cashback')->nullable();
            $table->timestamp('promo_limit')->nullable();
            $table->timestamp('promo_limit2')->nullable();
            $table->timestamp('fs_promo_limit')->nullable();
            $table->timestamp('fs_promo_limit2')->nullable();
            $table->double('bonus_bank')->nullable();
            $table->integer('repost')->default(0);
            $table->float('bonus_balance', 16, 2)->default(0.00);
            $table->double('wager', 16, 2)->default(0.00);
            $table->double('slots_wager', 16, 2);
            $table->integer('wager_status')->default(1);
            $table->string('avatar', 300)->nullable();
            $table->bigInteger('vk_id')->nullable();
            $table->string('tg_id', 50)->default('0');
            $table->string('vk_username', 255)->nullable();
            $table->double('dice', 16, 2)->default(0.00);
            $table->double('mines', 16, 2)->default(0.00);
            $table->double('bubbles', 16, 2)->default(0.00);
            $table->double('wheel', 16, 2)->default(0.00);
            $table->double('slots', 16, 2)->default(0.00)->nullable();
            $table->double('plinko');
            $table->integer('total_reposts')->default(0);
            $table->boolean('is_bot')->default(0);
            $table->integer('is_admin')->default(0);
            $table->integer('is_youtuber')->default(0);
            $table->integer('is_worker')->default(0);
            $table->text('admin_role')->nullable();
            $table->integer('referral_use')->default(0)->nullable();
            $table->integer('referral_send')->default(0);
            $table->double('referral_balance', 16, 2)->default(0.00);
            $table->double('ref_1_lvl', 16, 2)->default(0.00);
            $table->double('ref_2_lvl', 16, 2)->default(0.00);
            $table->double('ref_3_lvl', 16, 2)->default(0.00);
            $table->integer('ban')->default(0);
            $table->integer('limit_payment')->default(0);
            $table->string('auth_token', 255)->nullable();
            $table->string('game_token', 255)->nullable();
            $table->string('game_token_date', 255)->nullable();
            $table->string('current_currency', 255)->nullable();
            $table->integer('bonus_use')->default(0);
            $table->bigInteger('bonus_daily')->default(0);
            $table->bigInteger('bonus_hourly')->default(0);
            $table->integer('vk_bonus_use')->default(0);
            $table->integer('tg_bonus_use')->default(0);
            $table->string('created_ip', 255)->nullable();
            $table->string('used_ip', 255)->nullable();
            $table->string('videocard', 255)->nullable();
            $table->string('fingerprint', 255)->nullable();
            $table->string('logs_length', 255)->nullable();
            $table->integer('current_id')->nullable();
            $table->double('current_bet', 16, 2)->nullable();
            $table->string('remember_token', 100)->default('');
            $table->integer('auto_withdraw')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Индексы
            $table->index('updated_at');

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
        Schema::dropIfExists('users');
    }
};