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
            $table->string('shopify_id')->unique();
            $table->string('name'); // Order Number ex: #1001
            $table->string('email')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('status'); // paid, pending, etc.
            $table->json('items'); // JSON storage for line items (snapshot)
            $table->timestamp('order_date');
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
