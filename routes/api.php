<?php

use App\Http\Controllers\DelhiveryController;
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
    Route::post('/orders/generatePickup', [ShiprocketController::class, 'generatePickup']);

    Route::get('/shipments/track/shipment/{shipmentId}', [ShiprocketController::class, 'trackShipment']);
    Route::get('/shipments/track/awb/{awb}', [ShiprocketController::class, 'trackShipmentAWB']);
    Route::get('/shipments/track/order', [ShiprocketController::class, 'trackShipmentOrder']);
    Route::get('/shipments/track/awbs', [ShiprocketController::class, 'trackMultipleShipmentAWB']);


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




    // Delhivery

    Route::get('/delhivery',[DelhiveryController::class, 'index']);


    Route::get('/bench', function () {
        // heavy dummy work to test CPU & concurrency
        $sum = 0;
        for ($i = 0; $i < 200000; $i++) {
            $sum += $i;
        }

        return [
            'status' => 'ok',
            'worker' => getmypid(),
            'sum' => $sum,
            'timestamp' => microtime(true),
        ];
    });

    Route::get('/sse', function () {
        return response()->stream(function () {
            for ($i = 1; $i <= 10; $i++) {
                echo "data: " . json_encode(['tick' => $i]) . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
                sleep(2);
            }
        },200,['Cache-Control' => 'no-store',
            'Content-Type'  => 'text/event-stream',
            'X-Accel-Buffering' => 'no',]);
    });



});

Route::post('/webhook', [WebhookController::class, 'webhook']);
