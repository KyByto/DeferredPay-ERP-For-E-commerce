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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('shopify')->after('order_date');
            $table->string('canal_messages')->nullable()->after('source');
            $table->string('customer_name')->nullable()->after('canal_messages');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('customer_address')->nullable()->after('customer_phone');
            $table->text('notes')->nullable()->after('customer_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'canal_messages',
                'customer_name',
                'customer_phone',
                'customer_address',
                'notes',
            ]);
        });
    }
};
