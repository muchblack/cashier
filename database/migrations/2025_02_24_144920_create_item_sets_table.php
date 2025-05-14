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
        Schema::create('item_sets', function (Blueprint $table) {
            $table->id();
            $table->integer('owner_id')->comment('建立者id');
            $table->string('item_set_name',50)->comment('商品組合名稱');
            $table->string('item_set_name_en',50)->comment('商品組合名稱(英文)')->nullable();
            $table->string('item_set_name_jp',50)->comment('商品組合名稱(中文)')->nullable();
            $table->integer('item_set_price')->comment('組合售價')->default(0);
            $table->integer('item_set_stock')->comment('組合庫存')->default(0);
            $table->json('item_set_list')->comment('組合產品列表');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_sets');
    }
};
