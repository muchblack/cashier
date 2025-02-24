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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 50)->comment('商品名稱(中文)');
            $table->string('item_name_en', 50)->comment('商品名稱(英文)')->nullable();
            $table->string('item_name_jp', 50)->comment('商品名稱(日文)')->nullable();
            $table->integer('item_type_id')->comment('商品類別');
            $table->integer('item_price')->comment('商品價格')->default(0);
            $table->integer('item_stock')->comment('商品庫存')->default(0);
            $table->integer('is_r18')->comment('是否爲R18商品')->default(0);
            $table->text('item_img_url')->comment('商品圖片');
            $table->integer('owner_id')->comment('產品擁有者');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
