<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('returned_sold')->nullable()->after('notes');
        });

        // Migrate existing returned_products data into orders.returned_sold
        $returnedOrders = \App\Models\Order::where('status', 'returned')->get();
        foreach ($returnedOrders as $order) {
            $items = $order->items ?? [];
            $returnedSold = [];
            foreach ($items as $item) {
                $returnedSold[] = ['sold' => 0, 'removed' => 0];
            }
            $order->update(['returned_sold' => $returnedSold]);
        }

        Schema::dropIfExists('returned_products');
    }

    public function down(): void
    {
        Schema::create('returned_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->string('status')->default('en_stock');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('returned_sold');
        });
    }
};
