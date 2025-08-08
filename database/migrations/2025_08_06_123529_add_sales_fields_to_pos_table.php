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
        Schema::table('pos', function (Blueprint $table) {
            $table->string('order_type')->nullable()->after('category_id');
            $table->decimal('gross_amount', 10, 2)->nullable()->after('order_type');
            $table->decimal('platform_fee', 10, 2)->nullable()->after('gross_amount');
            $table->decimal('net_amount', 10, 2)->nullable()->after('platform_fee');
            $table->string('delivery_time')->nullable()->after('net_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'gross_amount', 'platform_fee', 'net_amount', 'delivery_time']);
        });
    }
};
