<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $baseUrl;
    protected ?string $initialToken;
    protected ?string $clientId;
    protected ?string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.shopify.url');
        $this->initialToken = config('services.shopify.token');
        $this->clientId = config('services.shopify.client_id');
        $this->clientSecret = config('services.shopify.client_secret');
    }

    /**
     * Retrieve the current valid Access Token (from Cache or Config).
     */
    protected function getAccessToken(): string
    {
        // 1. Check Cache (priority to refreshed tokens)
        $cachedToken = Cache::get('shopify_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        // 2. Fallback to .env token
        return $this->initialToken ?? '';
    }

    /**
     * Refresh the Access Token using Client Credentials flow.
     */
    protected function refreshAccessToken(): ?string
    {
        Log::info('ShopifyService: Attempting to refresh access token...');

        if (!$this->clientId || !$this->clientSecret) {
            Log::error('ShopifyService: Missing Client ID or Secret for token refresh.');
            return null;
        }

        try {
            // POST request to refresh token
            $response = Http::post("{$this->baseUrl}/admin/oauth/access_token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $newToken = $data['access_token'] ?? null;
                // Tokens usually last a while, but let's cache it safely for 24h or use 'expires_in' if provided
                $expiresIn = $data['expires_in'] ?? 86400; 

                if ($newToken) {
                    Cache::put('shopify_access_token', $newToken, $expiresIn);
                    Log::info('ShopifyService: Token refreshed successfully.');
                    return $newToken;
                }
            }
            
            Log::error('ShopifyService: Failed to refresh token.', ['response' => $response->body()]);

        } catch (\Exception $e) {
            Log::error('ShopifyService: Exception during token refresh: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Centralized method to send GraphQL requests with Auto-Refresh logic.
     */
    protected function sendGraphQLRequest(string $query, int $retryCount = 0): array
    {
        if (!$this->baseUrl) {
            return ['errors' => [['message' => 'Shopify URL not configured']]];
        }

        $token = $this->getAccessToken();

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/admin/api/2024-01/graphql.json", [
            'query' => $query
        ]);

        // Handle 401 Unauthorized (Expired Token)
        if ($response->status() === 401 && $retryCount < 1) {
            Log::warning('ShopifyService: 401 Unauthorized. Triggering token refresh.');
            
            $newToken = $this->refreshAccessToken();
            
            if ($newToken) {
                // Retry the request ONCE with the new token
                return $this->sendGraphQLRequest($query, $retryCount + 1);
            }
        }

        if ($response->failed()) {
            Log::error('Shopify GraphQL Error', ['body' => $response->body()]);
            throw new \Exception('Failed to fetch data from Shopify.');
        }

        return $response->json();
    }

    public function getConfirmedOrders(?string $lastSyncDate = null): array
    {
        $allOrders = [];
        $cursor = null;
        $hasNextPage = true;

        // Base search query: We now look for FULFILLED (Traité) orders regardless of payment status
        $searchQuery = "fulfillment_status:fulfilled";
        if ($lastSyncDate) {
            $searchQuery .= " AND created_at:>'{$lastSyncDate}'";
        }

        while ($hasNextPage) {
            // Build GraphQL Query with dynamic cursor and search filter
            $afterClause = $cursor ? ", after: \"{$cursor}\"" : "";
            
            $query = <<<GRAPHQL
            {
                orders(first: 50, query: "{$searchQuery}"{$afterClause}) {
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                    edges {
                        node {
                            id
                            name
                            email
                            totalPriceSet {
                                shopMoney {
                                    amount
                                }
                            }
                            totalShippingPriceSet {
                                shopMoney {
                                    amount
                                }
                            }
                            currentSubtotalPriceSet {
                                shopMoney {
                                    amount
                                }
                            }
                            createdAt
                            displayFinancialStatus
                            displayFulfillmentStatus
                            lineItems(first: 50) {
                                edges {
                                    node {
                                        title
                                        quantity
                                        sku
                                        originalUnitPriceSet {
                                            shopMoney {
                                                amount
                                            }
                                        }
                                        image {
                                            url
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
GRAPHQL;

            // Use the centralized request method
            $data = $this->sendGraphQLRequest($query);

            if (isset($data['errors'])) {
                Log::error('Shopify GraphQL Logic Error', ['errors' => $data['errors']]);
                throw new \Exception('Shopify GraphQL returned errors.');
            }

            $edges = $data['data']['orders']['edges'] ?? [];
            $allOrders = array_merge($allOrders, $edges);

            // Pagination Logic
            $pageInfo = $data['data']['orders']['pageInfo'] ?? [];
            $hasNextPage = $pageInfo['hasNextPage'] ?? false;
            $cursor = $pageInfo['endCursor'] ?? null;
        }

        return $allOrders;
    }
}
