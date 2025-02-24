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
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            $table->string('item_type_name',50)->comment('類別名稱(中文)');
            $table->string('item_type_name_en',50)->comment('類別名稱(英文)')->nullable();
            $table->string('item_type_name_jp',50)->comment('類別名稱(日文)')->nullable();
            $table->string('owner_id')->comment('擁有者');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
