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
            $table->integer('owner_id')->comment('建立者ID');
            $table->string('payment_name', 50)->comment('付款類別名稱');
            $table->string('payment_name_en', 50)->comment('付款類別名稱(中文)')->nullable();
            $table->string('payment_name_jp', 50)->comment('付款類別名稱(日文)')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
