<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_phone_alt')->nullable()->after('customer_phone');
            $table->text('billing_address')->nullable()->after('city');
            $table->string('billing_city', 100)->nullable()->after('billing_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_phone_alt', 'billing_address', 'billing_city']);
        });
    }
};
