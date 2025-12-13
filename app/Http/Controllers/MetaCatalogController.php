<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMetaCatalogBatch;
use App\Services\MetaCatalogService;
use Illuminate\Http\Request;

class MetaCatalogController extends Controller
{
    public MetaCatalogService $metaCatalogService;

    public function __construct(MetaCatalogService $metaCatalogService)
    {
        $this->metaCatalogService = $metaCatalogService;
    }

    // ==========================================
    // CATALOG MANAGEMENT
    // ==========================================

    public function getCatalog(Request $request)
    {
        $validated = $request->validate([
            'catalog_id' => 'nullable|string',
        ]);

        $result = $this->metaCatalogService->getCatalog($validated['catalog_id'] ?? null);

        if ($result['success']) {
            return response()->json($result['data'], 200);
        }

        return response()->json(['error' => 'Failed to get catalog', 'details' => $result['error']], 500);
    }

    public function updateCatalog(Request $request)
    {
        $validated = $request->validate([
            'catalog_id' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'vertical' => 'nullable|string|in:commerce,hotel,flight,destination,home_listing,vehicle,media_title',
        ]);

        $catalogId = $validated['catalog_id'] ?? null;
        unset($validated['catalog_id']);

        $result = $this->metaCatalogService->updateCatalog($validated, $catalogId);

        if ($result['success']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['error' => 'Failed to update catalog', 'details' => $result['error']], 500);
    }

    // ==========================================
    // PRODUCT MANAGEMENT
    // ==========================================

    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'availability' => 'required|string|in:in stock,out of stock,preorder,available for order,discontinued',
            'condition' => 'required|string|in:new,refurbished,used',
            'brand' => 'nullable|string|max:100',
            'link' => 'required|url|max:1024',
            'image' => 'required|array',
            'additional_image_urls' => 'nullable|array',
            'additional_image_urls.*' => 'url|max:1024',
            'custom_label_0' => 'nullable|string|max:100',
            'custom_label_1' => 'nullable|string|max:100',
            'custom_label_2' => 'nullable|string|max:100',
            'custom_label_3' => 'nullable|string|max:100',
            'custom_label_4' => 'nullable|string|max:100',
            'google_product_category' => 'nullable|string|max:250',
            'product_type' => 'nullable|string|max:750',
        ]);

        $result = $this->metaCatalogService->createProduct($validated);

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json(['error' => 'Failed to create product', 'details' => $result['error']], 500);
    }

    public function getProduct(string $productId)
    {
        $result = $this->metaCatalogService->getProduct($productId);

        if ($result['success']) {
            return response()->json($result['data'], 200);
        }

        return response()->json(['error' => 'Failed to get product', 'details' => $result['error']], 404);
    }

    public function updateProduct(Request $request, string $productId)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'availability' => 'nullable|string|in:in stock,out of stock,preorder,available for order,discontinued',
            'condition' => 'nullable|string|in:new,refurbished,used',
            'brand' => 'nullable|string|max:100',
            'url' => 'nullable|url|max:1024',
            'image_url' => 'nullable|url|max:1024',
            'additional_image_urls' => 'nullable|array',
            'additional_image_urls.*' => 'url|max:1024',
        ]);

        $result = $this->metaCatalogService->updateProduct($productId, $validated);

        if ($result['success']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['error' => 'Failed to update product', 'details' => $result['error']], 500);
    }

    public function deleteProduct(string $productId)
    {
        $result = $this->metaCatalogService->deleteProduct($productId);

        if ($result['success']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['error' => 'Failed to delete product', 'details' => $result['error']], 500);
    }

    public function listProducts(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:500',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $filters = [];
        if (isset($validated['filter'])) {
            $filters['filter'] = $validated['filter'];
        }
        if (isset($validated['limit'])) {
            $filters['limit'] = $validated['limit'];
        }

        $result = $this->metaCatalogService->listProducts($filters);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json(['error' => 'Failed to list products', 'details' => $result['error']], 500);
    }

    // ==========================================
    // BATCH OPERATIONS
    // ==========================================

    public function batchCreateProducts(Request $request)
    {
        // Validation commented out to accept raw request
        $validated = $request->validate([
            'products' => 'required|array|min:1|max:50',
            'products.*.id' => 'required|string',
            'products.*.title' => 'required|string',
            'products.*.name' => 'nullable|string|max:255',
            'products.*.description' => 'nullable|string|max:5000',
            'products.*.price' => 'nullable|numeric|min:0',
            'products.*.availability' => 'nullable|string|in:in stock,out of stock,preorder,available for order,discontinued',
            'products.*.link' => 'nullable|string',
            'products.*.condition' => 'nullable|string',
            'products.*.brand' => 'nullable|string',
            'products.*.image' => 'nullable|array',
        ]);

        ProcessMetaCatalogBatch::dispatch($validated['products'])->onQueue('low');

        return response()->json(['success' => true, 'details' => 'Batch is Queued'], 201);
    }

    public function batchDeleteProducts(Request $request)
    {
        $validated = $request->validate([
            'retailer_ids' => 'required|array|min:1|max:100',
            'retailer_ids.*' => 'required|string',
        ]);

        $result = $this->metaCatalogService->batchDeleteProducts($validated['retailer_ids']);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json(['error' => 'Batch delete failed', 'details' => $result['error']], 500);
    }

    public function batchOperations(Request $request)
    {
        $validated = $request->validate([
            'operations' => 'required|array|min:1|max:100',
            'operations.*.method' => 'required|string|in:CREATE,UPDATE,DELETE',
            'operations.*.retailer_id' => 'required|string',
            'operations.*.data' => 'sometimes|array',
        ]);

        $result = $this->metaCatalogService->batchOperations($validated['operations']);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json(['error' => 'Batch operations failed', 'details' => $result['error']], 500);
    }

    // ==========================================
    // PRODUCT SETS
    // ==========================================

    public function createProductSet(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filter' => 'nullable|string|max:1000',
            'product_catalog_id' => 'nullable|string',
        ]);

        $result = $this->metaCatalogService->createProductSet($validated);

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json(['error' => 'Failed to create product set', 'details' => $result['error']], 500);
    }

    public function updateProductSet(Request $request, string $setId)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:1000',
        ]);

        $result = $this->metaCatalogService->updateProductSet($setId, $validated);

        if ($result['success']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['error' => 'Failed to update product set', 'details' => $result['error']], 500);
    }

    public function deleteProductSet(string $setId)
    {
        $result = $this->metaCatalogService->deleteProductSet($setId);

        if ($result['success']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['error' => 'Failed to delete product set', 'details' => $result['error']], 500);
    }
}
