<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('allow_sell_alone')->default(true)->after('min_order_qty');
        });

        Schema::create('product_companion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('companion_product_id')->constrained('products')->cascadeOnDelete();
            $table->unique(['product_id', 'companion_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_companion');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('allow_sell_alone');
        });
    }
};
