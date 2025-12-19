<?php

use App\Http\Controllers\DelhiveryController;
use App\Http\Controllers\DelhiveryWebhookController;
use App\Http\Controllers\MetaCatalogController;
use App\Http\Controllers\ShiprocketController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Octane\Facades\Octane;
use Illuminate\Support\Facades\Cache;

Route::post('/login', \App\Http\Controllers\Api\Auth\LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/orders/create', [ShiprocketController::class, 'createOrder']);
    Route::post('/orders/update', [ShiprocketController::class, 'updateOrder']);
    Route::post('/orders/create/return', [ShiprocketController::class, 'createReturnOrder']);
    Route::post('/orders/return/edit', [ShiprocketController::class, 'editReturnOrder']);
    Route::get('/orders/return', [ShiprocketController::class, 'getAllReturnOrders']);
    Route::post('/orders/create/exchange', [ShiprocketController::class, 'createExchangeOrder']);
    Route::post('/orders/cancel', [ShiprocketController::class, 'cancelOrder']);

    Route::post('/orders/generateAWB', [ShiprocketController::class, 'generateAWB']);
    Route::post('/orders/generateManifest', [ShiprocketController::class, 'generateManifest']);
    Route::post('/orders/generateLabel', [ShiprocketController::class, 'generateLabel']);
    Route::post('/orders/generateInvoice', [ShiprocketController::class, 'generateInvoice']);
    Route::post('/orders/generatePickup', [ShiprocketController::class, 'generatePickup']);

    Route::get('/shipments/track/shipment/{shipmentId}', [ShiprocketController::class, 'trackShipment']);
    Route::get('/shipments/track/awb/{awb}', [ShiprocketController::class, 'trackShipmentAWB']);
    Route::get('/shipments/track/order', [ShiprocketController::class, 'trackShipmentOrder']);
    Route::post('/shipments/track/awbs', [ShiprocketController::class, 'trackMultipleShipmentAWB']);

    Route::post('/shipments/cancel/', [ShiprocketController::class, 'cancelShipment']);
    Route::get('/shipments/orders/{orderId}', [ShiprocketController::class, 'getOrder']);
    Route::get('/shipments/orders', [ShiprocketController::class, 'getAllOrders']);
    Route::get('/shipments/couriers', [ShiprocketController::class, 'getAllCouriers']);
    Route::get('/shipments/serviceability', [ShiprocketController::class, 'serviceability']);

    Route::post('/shipments/create', [ShiprocketController::class, 'createForwordShipment']);
    Route::post('/shipments/return/create', [ShiprocketController::class, 'createReturnShipment']);

    Route::get('/warehouse', [ShiprocketController::class, 'getAllPickup']);
    Route::post('/warehouse/create', [ShiprocketController::class, 'addNewPickup']);

    Route::get('/wallet', [ShiprocketController::class, 'getWalletBalance']);

    Route::get('/products', [ShiprocketController::class, 'getAllProducts']);
    Route::get('/products/{product_id}', [ShiprocketController::class, 'getProductDetails']);
    Route::post('/products', [ShiprocketController::class, 'addNewProduct']);

    Route::post('/inventory', [ShiprocketController::class, 'getInventory']);
    Route::put('/inventory/{product_id}', [ShiprocketController::class, 'updateInventory']);

    Route::get('/statement', [ShiprocketController::class, 'getStatement']);

    Route::get('/discrepancy', [ShiprocketController::class, 'getDiscrepancy']);

    Route::post('/queue', [ShiprocketController::class, 'demoQueue']);

    // ==========================================
    // Delhivery - Orders
    // ==========================================
    Route::post('/delhivery/orders/create', [DelhiveryController::class, 'createOrder']);
    Route::put('/delhivery/orders/edit/{waybill}', [DelhiveryController::class, 'editShipment']);
    Route::delete('/delhivery/orders/cancel/{waybill}', [DelhiveryController::class, 'cancelShipment']);

    // ==========================================
    // Delhivery - Tracking
    // ==========================================
    Route::get('/delhivery/track/{waybill}', [DelhiveryController::class, 'trackShipment']);
    Route::post('/delhivery/track/multiple', [DelhiveryController::class, 'trackMultipleShipments']);

    // ==========================================
    // Delhivery - Labels & Documents
    // ==========================================
    Route::get('/delhivery/label/{waybill}', [DelhiveryController::class, 'generateLabel']);
    Route::get('/delhivery/waybill/{clientOrderId}', [DelhiveryController::class, 'fetchWaybill']);

    // ==========================================
    // Delhivery - Pickup & Logistics
    // ==========================================
    Route::post('/delhivery/pickup/create', [DelhiveryController::class, 'createPickupRequest']);
    Route::get('/delhivery/serviceability', [DelhiveryController::class, 'checkServiceability']);
    Route::post('/delhivery/calculate-cost', [DelhiveryController::class, 'calculateShippingCost']);
    Route::get('/delhivery/pincode-serviceability', [DelhiveryController::class, 'pincodeServiceability']);

    // ==========================================
    // Delhivery - NDR Management
    // ==========================================
    Route::put('/delhivery/ndr/update/{waybill}', [DelhiveryController::class, 'updateNDRShipment']);

    // ==========================================
    // Meta Catalog - Catalog Management
    // ==========================================
    Route::get('/meta-catalog/catalog', [MetaCatalogController::class, 'getCatalog']);
    Route::put('/meta-catalog/catalog', [MetaCatalogController::class, 'updateCatalog']);

    // ==========================================
    // Meta Catalog - Product Management
    // ==========================================
    Route::get('/meta-catalog/products', [MetaCatalogController::class, 'listProducts']);
    Route::post('/meta-catalog/products', [MetaCatalogController::class, 'createProduct']);
    Route::get('/meta-catalog/products/{productId}', [MetaCatalogController::class, 'getProduct']);
    Route::put('/meta-catalog/products/{productId}', [MetaCatalogController::class, 'updateProduct']);
    Route::delete('/meta-catalo§g/products/{productId}', [MetaCatalogController::class, 'deleteProduct']);

    // ==========================================
    // Meta Catalog - Batch Operations
    // ==========================================
    Route::post('/meta-catalog/products/batch', [MetaCatalogController::class, 'batchCreateProducts']);
    Route::put('/meta-catalog/products/batch', [MetaCatalogController::class, 'batchUpdateProducts']);
    Route::delete('/meta-catalog/products/batch', [MetaCatalogController::class, 'batchDeleteProducts']);
    Route::post('/meta-catalog/batch/operations', [MetaCatalogController::class, 'batchOperations']);

    // ==========================================
    // Meta Catalog - Product Sets
    // ==========================================
    Route::post('/meta-catalog/product-sets', [MetaCatalogController::class, 'createProductSet']);
    Route::put('/meta-catalog/product-sets/{setId}', [MetaCatalogController::class, 'updateProductSet']);
    Route::delete('/meta-catalog/product-sets/{setId}', [MetaCatalogController::class, 'deleteProductSet']);


    Route::post('/upload', [WebhookController::class, 'uploadToR2']);






    Route::get('/invoice/generate/{order_id}', [\App\Http\Controllers\InvoiceController::class, 'generateInvoice']);
});

Route::post('/webhook', [WebhookController::class, 'webhook']);
Route::post('/webhook/delhivery', [DelhiveryWebhookController::class, 'handle']);
Route::post('/syncorder/{order_id}', [WebhookController::class, 'syncorder']);
Route::post('/syncAllOrder', [WebhookController::class, 'syncAllOrder']);





