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
        Schema::create('cashback', function (Blueprint $table) {
            $table->integer('id'); // без AUTO_INCREMENT и PK, как в исходной схеме
            $table->integer('user_id');
            $table->double('amount');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
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
