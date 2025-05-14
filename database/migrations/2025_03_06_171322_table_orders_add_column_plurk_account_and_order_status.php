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
        //
        Schema::table('orders', function (Blueprint $table) {
           $table->enum('status',['payed','nonpayed','preorder'])->change();
           $table->string('plurk_account', 50)->nullable()->after('preorder_name')->comment('噗浪帳號');
           $table->integer('preorder_price')->nullable()->after('plurk_account')->comment('預付定金');
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
