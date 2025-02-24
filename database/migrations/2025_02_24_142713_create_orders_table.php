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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('owner_id')->comment('使用者ID');
            $table->enum('order_type',['preorder','onsite'])->comment('訂單類別');
            $table->enum('status',['payed','nonpayed'])->comment('付款狀態 已付/未付');
            $table->integer('order_amount')->comment('訂單總價');
            $table->json('item_lists')->comment('商品列表');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
