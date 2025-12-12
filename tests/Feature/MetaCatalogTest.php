<?php

use App\Models\User;
use App\Services\MetaCatalogService;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Mock Meta API configuration
    Config::set('services.meta.access_token', 'test_access_token');
    Config::set('services.meta.catalog_id', 'test_catalog_id');

    // Create a test user
    $this->user = User::factory()->create();
});

it('requires authentication to access meta catalog endpoints', function () {
    $response = $this->getJson('/api/meta-catalog/catalog');

    $response->assertUnauthorized();
});

it('can get catalog information', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('getCatalog')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'id' => 'test_catalog_id',
                    'name' => 'Test Catalog',
                    'product_count' => 100,
                ],
            ]);
    });

    $response = actingAs($this->user)->getJson('/api/meta-catalog/catalog');

    $response->assertSuccessful()
        ->assertJson([
            'id' => 'test_catalog_id',
            'name' => 'Test Catalog',
        ]);
});

it('can create a product', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('createProduct')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['product_id' => 'prod_123'],
                'message' => 'Product created successfully',
            ]);
    });

    $productData = [
        'retailer_id' => 'SKU123',
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 99.99,
        'currency' => 'USD',
        'availability' => 'in stock',
        'condition' => 'new',
        'url' => 'https://example.com/product',
        'image_url' => 'https://example.com/image.jpg',
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/products', $productData);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => ['product_id' => 'prod_123'],
        ]);
});

it('validates product creation data', function () {
    $response = actingAs($this->user)->postJson('/api/meta-catalog/products', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['retailer_id', 'name', 'price', 'currency', 'availability', 'condition', 'url', 'image_url']);
});

it('validates product price is numeric', function () {
    $productData = [
        'retailer_id' => 'SKU123',
        'name' => 'Test Product',
        'price' => 'invalid',
        'currency' => 'USD',
        'availability' => 'in stock',
        'condition' => 'new',
        'url' => 'https://example.com/product',
        'image_url' => 'https://example.com/image.jpg',
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/products', $productData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});

it('can update a product', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('updateProduct')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);
    });

    $updateData = [
        'name' => 'Updated Product Name',
        'price' => 129.99,
    ];

    $response = actingAs($this->user)->putJson('/api/meta-catalog/products/prod_123', $updateData);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Product updated successfully']);
});

it('can delete a product', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('deleteProduct')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
    });

    $response = actingAs($this->user)->deleteJson('/api/meta-catalog/products/prod_123');

    $response->assertSuccessful()
        ->assertJson(['message' => 'Product deleted successfully']);
});

it('can list products', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('listProducts')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    ['id' => 'prod_1', 'name' => 'Product 1'],
                    ['id' => 'prod_2', 'name' => 'Product 2'],
                ],
                'count' => 2,
            ]);
    });

    $response = actingAs($this->user)->getJson('/api/meta-catalog/products');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data',
            'count',
        ]);
});

it('can batch create products', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('batchCreateProducts')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [],
                'summary' => [
                    'total' => 2,
                    'success' => 2,
                    'failed' => 0,
                ],
            ]);
    });

    $productsData = [
        'products' => [
            [
                'retailer_id' => 'SKU123',
                'name' => 'Product 1',
                'price' => 99.99,
                'currency' => 'USD',
                'availability' => 'in stock',
                'condition' => 'new',
                'url' => 'https://example.com/product1',
                'image_url' => 'https://example.com/image1.jpg',
            ],
            [
                'retailer_id' => 'SKU124',
                'name' => 'Product 2',
                'price' => 79.99,
                'currency' => 'USD',
                'availability' => 'in stock',
                'condition' => 'new',
                'url' => 'https://example.com/product2',
                'image_url' => 'https://example.com/image2.jpg',
            ],
        ],
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/products/batch', $productsData);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'data',
            'summary' => ['total', 'success', 'failed'],
        ]);
});

it('validates availability values', function () {
    $productData = [
        'retailer_id' => 'SKU123',
        'name' => 'Test Product',
        'price' => 99.99,
        'currency' => 'USD',
        'availability' => 'invalid_status',
        'condition' => 'new',
        'url' => 'https://example.com/product',
        'image_url' => 'https://example.com/image.jpg',
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/products', $productData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['availability']);
});

it('can create a product set', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('createProductSet')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['product_set_id' => 'set_123'],
                'message' => 'Product set created successfully',
            ]);
    });

    $setData = [
        'name' => 'Summer Collection',
        'filter' => '{"retailer_id":{"is_any":["SKU123","SKU124"]}}',
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/product-sets', $setData);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => ['product_set_id' => 'set_123'],
        ]);
});

it('handles service errors gracefully', function () {
    $this->mock(MetaCatalogService::class, function ($mock) {
        $mock->shouldReceive('createProduct')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'API Error: Invalid access token',
            ]);
    });

    $productData = [
        'retailer_id' => 'SKU123',
        'name' => 'Test Product',
        'price' => 99.99,
        'currency' => 'USD',
        'availability' => 'in stock',
        'condition' => 'new',
        'url' => 'https://example.com/product',
        'image_url' => 'https://example.com/image.jpg',
    ];

    $response = actingAs($this->user)->postJson('/api/meta-catalog/products', $productData);

    $response->assertStatus(500)
        ->assertJson(['error' => 'Failed to create product']);
});
