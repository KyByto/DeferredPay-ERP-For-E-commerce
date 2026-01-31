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
            // Get the date of the last imported order to optimize the query
            $lastOrderDate = Order::max('order_date');
            $lastSyncDate = $lastOrderDate ? Carbon::parse($lastOrderDate)->toIso8601String() : null;

            if ($lastSyncDate) {
                Log::info("Fetching Shopify orders created after: $lastSyncDate");
            } else {
                Log::info("Performing full initial sync of Shopify orders.");
            }

            $ordersEdges = $shopify->getConfirmedOrders($lastSyncDate);
            
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

                Order::updateOrCreate(
                    ['shopify_id' => $shopifyId],
                    [
                        'name' => $node['name'],
                        'email' => $node['email'] ?? '',
                        'total_price' => $node['totalPriceSet']['shopMoney']['amount'] ?? 0,
                        'subtotal_price' => $node['currentSubtotalPriceSet']['shopMoney']['amount'] ?? 0,
                        'shipping_price' => $node['totalShippingPriceSet']['shopMoney']['amount'] ?? 0,
                        'status' => strtolower($node['displayFinancialStatus'] ?? 'pending'),
                        'items' => $items,
                        'order_date' => Carbon::parse($node['createdAt']),
                    ]
                );
            }
            
            Log::info('Shopify Sync Completed. ' . count($ordersEdges) . ' orders processed.');
            
        } catch (\Exception $e) {
            Log::error('Shopify Sync Failed: ' . $e->getMessage());
        }
    }
}