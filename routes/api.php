<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShiprocketController;
Route::post('/login', \App\Http\Controllers\Api\Auth\LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/orders/create', [ShiprocketController::class, 'createOrder']);
    Route::post('/orders/create/return', [ShiprocketController::class, 'createReturnOrder']);
    Route::post('/orders/return/edit', [ShiprocketController::class, 'editReturnOrder']);
    Route::get('/orders/return', [ShiprocketController::class, 'getAllReturnOrders']);
    Route::post('/orders/create/exchange', [ShiprocketController::class, 'createExchangeOrder']);
    Route::post('/orders/cancel', [ShiprocketController::class, 'cancelOrder']);


    Route::post('/orders/generateAWB', [ShiprocketController::class, 'generateAWB']);
    Route::post('/orders/generateManifest', [ShiprocketController::class, 'generateManifest']);
    Route::post('/orders/generateLabel', [ShiprocketController::class, 'generateLabel']);
    Route::post('/orders/generateInvoice', [ShiprocketController::class, 'generateInvoice']);

    Route::get('/shipments/track/{shipmentId}', [ShiprocketController::class, 'trackShipment']);
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



    Route::post('/webhook', [WebhookController::class, 'webhook']);


    // put your protected API routes here
});


