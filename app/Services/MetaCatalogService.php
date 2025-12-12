<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCatalogService
{
    protected string $accessToken;

    protected string $catalogId;

    protected string $graphApiVersion = 'v19.0';

    protected string $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.meta.access_token');
        $this->catalogId = config('services.meta.catalog_id');
        $this->baseUrl = "https://graph.facebook.com/{$this->graphApiVersion}";
    }

    // ==========================================
    // CATALOG MANAGEMENT
    // ==========================================

    public function getCatalog(?string $catalogId = null): array
    {
        try {
            $id = $catalogId ?? $this->catalogId;

            $response = Http::get("{$this->baseUrl}/{$id}", [
                'access_token' => $this->accessToken,
                'fields' => 'id,name,business,product_count,vertical',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Get Catalog Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateCatalog(array $catalogData, ?string $catalogId = null): array
    {
        try {
            $id = $catalogId ?? $this->catalogId;

            $catalogData['access_token'] = $this->accessToken;

            $response = Http::post("{$this->baseUrl}/{$id}", $catalogData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Catalog updated successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Update Catalog Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // PRODUCT MANAGEMENT
    // ==========================================

    public function createProduct(array $productData): array
    {
        try {
            $productData['access_token'] = $this->accessToken;

            $response = Http::post("{$this->baseUrl}/{$this->catalogId}/products", $productData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'product_id' => $response->json()['id'] ?? null,
                    ],
                    'message' => 'Product created successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Create Product Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getProduct(string $productId): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/{$productId}", [
                'access_token' => $this->accessToken,
                'fields' => 'id,retailer_id,name,description,price,currency,availability,condition,brand,url,image_url,additional_image_urls',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Get Product Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateProduct(string $productId, array $productData): array
    {
        try {
            $productData['access_token'] = $this->accessToken;

            $response = Http::post("{$this->baseUrl}/{$productId}", $productData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Product updated successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Update Product Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deleteProduct(string $productId): array
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$productId}", [
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Product deleted successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Delete Product Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function listProducts(array $filters = []): array
    {
        try {
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'id,retailer_id,name,price,currency,availability,url,image_url',
            ];

            if (isset($filters['filter'])) {
                $params['filter'] = $filters['filter'];
            }

            if (isset($filters['limit'])) {
                $params['limit'] = $filters['limit'];
            }

            $response = Http::get("{$this->baseUrl}/{$this->catalogId}/products", $params);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'data' => $data['data'] ?? [],
                    'count' => count($data['data'] ?? []),
                    'paging' => $data['paging'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - List Products Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // BATCH OPERATIONS
    // ==========================================

    public function batchCreateProducts(array $products): array
    {

        try {
            // Format products for items_batch API with correct Meta field names
            $requests = [];
            foreach ($products as $productData) {
                // Transform to Meta's expected field names


                $metaPrice = number_format((float)$productData['price'], 2, '.', '') . " INR";
                $metaData = [
                    'id' => $productData['id'], // Meta uses 'id' not 'retailer_id'
                    'title' => $productData['title'], // Meta uses 'title' not 'name'
                    'description' => $productData['description'] ?? '',
                    'availability' => $productData['availability'],
                    'price' => $metaPrice,
                    'link' => $productData['link'], // Meta uses 'link' not 'url'
                    'image' => $productData['image'],
                ];
                // Add optional fields if present
                if (isset($productData['condition'])) {
                    $metaData['condition'] = $productData['condition'];
                }

                if (isset($productData['brand'])) {
                    $metaData['brand'] = $productData['brand'];
                }

                if (isset($productData['additional_image_urls'])) {
                    foreach ($productData['additional_image_urls'] as $additionalUrl) {
                        $metaData['image'][] = [
                            'url' => $additionalUrl,
                            'tag' => [],
                        ];
                    }
                }

                $requests[] = [
                    'method' => 'CREATE',
                    'data' => $metaData,
                ];
            }
            //dd($requests);

            $response = Http::asForm()->post("{$this->baseUrl}/{$this->catalogId}/items_batch", [
                'access_token' => $this->accessToken,
                'requests' => json_encode($requests),
                'item_type' => 'PRODUCT_ITEM', // Required parameter
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Batch product creation completed',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Batch Create Products Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function batchUpdateProducts(array $products): array
    {
        try {
            // Format products for items_batch API
            $requests = [];
            foreach ($products as $productUpdate) {
                if (! isset($productUpdate['retailer_id'])) {
                    continue; // Skip items without retailer_id
                }

                $retailerId = $productUpdate['retailer_id'];

                // Transform to Meta's expected field names
                $metaData = [];

                if (isset($productUpdate['name'])) {
                    $metaData['title'] = $productUpdate['name']; // Meta uses 'title'
                }

                if (isset($productUpdate['description'])) {
                    $metaData['description'] = $productUpdate['description'];
                }

                if (isset($productUpdate['price']) && isset($productUpdate['currency'])) {
                    $metaData['price'] = number_format((float) $productUpdate['price'], 2, '.', '').' '.$productUpdate['currency'];
                } elseif (isset($productUpdate['price'])) {
                    $metaData['price'] = number_format((float) $productUpdate['price'], 2, '.', '').' INR';
                }

                if (isset($productUpdate['availability'])) {
                    $metaData['availability'] = $productUpdate['availability'];
                }

                if (isset($productUpdate['url'])) {
                    $metaData['link'] = $productUpdate['url']; // Meta uses 'link'
                }

                if (isset($productUpdate['image_url'])) {
                    $metaData['image'] = [ // Meta uses 'image' array
                        [
                            'url' => $productUpdate['image_url'],
                            'tag' => [],
                        ],
                    ];
                }

                $requests[] = [
                    'method' => 'UPDATE',
                    'retailer_id' => $retailerId,
                    'data' => $metaData,
                ];
            }

            if (empty($requests)) {
                return [
                    'success' => false,
                    'error' => 'No valid products to update. retailer_id is required for each product.',
                ];
            }

            $response = Http::asForm()->post("{$this->baseUrl}/{$this->catalogId}/items_batch", [
                'access_token' => $this->accessToken,
                'requests' => json_encode($requests),
                'item_type' => 'PRODUCT_ITEM', // Required parameter
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Batch product update completed',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Batch Update Products Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function batchDeleteProducts(array $retailerIds): array
    {
        try {
            // Format products for items_batch API
            $requests = [];
            foreach ($retailerIds as $retailerId) {
                $requests[] = [
                    'method' => 'DELETE',
                    'retailer_id' => $retailerId,
                ];
            }

            $response = Http::asForm()->post("{$this->baseUrl}/{$this->catalogId}/items_batch", [
                'access_token' => $this->accessToken,
                'requests' => json_encode($requests),
                'item_type' => 'PRODUCT_ITEM',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Batch product deletion completed',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Batch Delete Products Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function batchOperations(array $operations): array
    {
        try {
            // Support mixed operations (CREATE, UPDATE, DELETE) in one batch
            $requests = [];

            foreach ($operations as $operation) {
                $method = strtoupper($operation['method'] ?? 'CREATE');

                if (! in_array($method, ['CREATE', 'UPDATE', 'DELETE'])) {
                    continue;
                }

                $request = ['method' => $method];

                if (isset($operation['retailer_id'])) {
                    $request['retailer_id'] = $operation['retailer_id'];
                }

                if (isset($operation['data']) && in_array($method, ['CREATE', 'UPDATE'])) {
                    $request['data'] = $operation['data'];
                }

                $requests[] = $request;
            }

            if (empty($requests)) {
                return [
                    'success' => false,
                    'error' => 'No valid operations provided',
                ];
            }

            $response = Http::asForm()->post("{$this->baseUrl}/{$this->catalogId}/items_batch", [
                'access_token' => $this->accessToken,
                'requests' => json_encode($requests),
                'item_type' => 'PRODUCT_ITEM',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Batch operations completed',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Batch Operations Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // PRODUCT SETS
    // ==========================================

    public function createProductSet(array $setData): array
    {
        try {
            $setData['access_token'] = $this->accessToken;

            if (! isset($setData['product_catalog_id'])) {
                $setData['product_catalog_id'] = $this->catalogId;
            }

            $response = Http::post("{$this->baseUrl}/{$this->catalogId}/product_sets", $setData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'product_set_id' => $response->json()['id'] ?? null,
                    ],
                    'message' => 'Product set created successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Create Product Set Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateProductSet(string $setId, array $setData): array
    {
        try {
            $setData['access_token'] = $this->accessToken;

            $response = Http::post("{$this->baseUrl}/{$setId}", $setData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Product set updated successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Update Product Set Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deleteProductSet(string $setId): array
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$setId}", [
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Product set deleted successfully',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            Log::error('Meta Catalog - Delete Product Set Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
