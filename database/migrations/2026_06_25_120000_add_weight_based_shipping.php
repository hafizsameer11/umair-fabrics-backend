<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->default(500)->after('featured');
        });

        Schema::table('shipping_settings', function (Blueprint $table) {
            $table->unsignedInteger('weight_min_grams')->default(100)->after('flat_rate');
            $table->unsignedInteger('weight_max_grams')->default(3000)->after('weight_min_grams');
            $table->decimal('base_fee', 12, 2)->nullable()->after('weight_max_grams');
            $table->unsignedInteger('extra_step_grams')->default(1000)->after('base_fee');
            $table->decimal('extra_step_fee', 12, 2)->default(50)->after('extra_step_grams');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });

        Schema::table('shipping_settings', function (Blueprint $table) {
            $table->dropColumn([
                'weight_min_grams',
                'weight_max_grams',
                'base_fee',
                'extra_step_grams',
                'extra_step_fee',
            ]);
        });
    }
};
