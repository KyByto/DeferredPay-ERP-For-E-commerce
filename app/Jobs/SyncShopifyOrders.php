<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ShopifyService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncShopifyOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ShopifyService $shopify): void
    {
        Log::info('Starting Shopify Sync...');

        try {
            // Sync by updated time to avoid missing late confirmations
            $startDate = Carbon::now()->subWeek()->toIso8601String();
            $endDate = Carbon::now()->toIso8601String();

            Log::info("Fetching Shopify orders updated between: $startDate and $endDate");

            $ordersEdges = $shopify->getConfirmedOrders($startDate, $endDate);

            foreach ($ordersEdges as $edge) {
                $node = $edge['node'];

                // Parse items from GraphQL structure
                $items = collect($node['lineItems']['edges'] ?? [])->map(function ($itemEdge) {
                    $itemNode = $itemEdge['node'];

                    return [
                        'name' => $itemNode['title'],
                        'quantity' => $itemNode['quantity'],
                        'sku' => $itemNode['sku'] ?? '',
                        'price' => $itemNode['originalUnitPriceSet']['shopMoney']['amount'] ?? 0,
                        'image_url' => $itemNode['image']['url'] ?? null,
                    ];
                })->toArray();

                // Clean up ID (GraphQL ID is a URI like gid://shopify/Order/12345)
                // We usually just want the numeric ID for storage, or store the whole string.
                // Let's store the numeric part to be consistent with typical DB INTs,
                // OR keep it as string if our DB column is string.
                // Migration `shopify_id` is likely BIGINT or String.
                // Let's extract numeric ID for safety if column is numeric.
                $shopifyId = basename($node['id']);

                $existing = Order::where('shopify_id', $shopifyId)->first();

                if ($existing) {
                    // Order already exists: only update fields that come from Shopify
                    // and do NOT touch internal state fields managed manually
                    // (status, canal_messages, customer_name, customer_phone,
                    //  customer_address, notes, returned_sold)
                    $existing->update([
                        'name' => $node['name'],
                        'email' => $node['email'] ?? '',
                        'total_price' => $node['totalPriceSet']['shopMoney']['amount'] ?? 0,
                        'subtotal_price' => $node['currentSubtotalPriceSet']['shopMoney']['amount'] ?? 0,
                        'shipping_price' => $node['totalShippingPriceSet']['shopMoney']['amount'] ?? 0,
                        'items' => $items,
                        'order_date' => Carbon::parse($node['createdAt']),
                    ]);
                } else {
                    // New order: insert with all fields including the initial Shopify status
                    Order::create([
                        'shopify_id' => $shopifyId,
                        'name' => $node['name'],
                        'email' => $node['email'] ?? '',
                        'total_price' => $node['totalPriceSet']['shopMoney']['amount'] ?? 0,
                        'subtotal_price' => $node['currentSubtotalPriceSet']['shopMoney']['amount'] ?? 0,
                        'shipping_price' => $node['totalShippingPriceSet']['shopMoney']['amount'] ?? 0,
                        'status' => strtolower($node['displayFinancialStatus'] ?? 'pending'),
                        'items' => $items,
                        'order_date' => Carbon::parse($node['createdAt']),
                        'source' => 'shopify',
                    ]);
                }
            }

            Log::info('Shopify Sync Completed. '.count($ordersEdges).' orders processed.');

        } catch (\Exception $e) {
            Log::error('Shopify Sync Failed: '.$e->getMessage());
        }
    }
}
