<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShiprocketService
{
    protected $token;

    public function authenticate()
    {
        if ($this->token) {
            return $this->token;
        }

        $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
            'email' => config('services.shiprocket.email'),
            'password' => config('services.shiprocket.password'),
        ]);

        //dd(json_decode($response->body()));
        if ($response->successful()) {
            $this->token = $response['token'];

            return $this->token;
        }

        throw new \Exception('Shiprocket Authentication failed');
    }

    public function createOrder(array $orderData)
    {

        $token = $this->authenticate();
        // dd($token);

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/create/adhoc', $orderData);
    }

    public function updateOrder(array $orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/update/adhoc', $orderData);
    }

    public function createReturnOrder(array $orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/create/return', $orderData);
    }

    public function editReturnOrder($orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/edit', $orderData);
    }

    public function getAllReturnOrders($filter)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/orders/processing/return', $filter);
    }

    public function generateAWB($orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/courier/assign/awb', $orderData);
    }

    public function generateManifest($orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/manifests/generate', $orderData);
    }

    public function generateLabel($orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/courier/generate/label', $orderData);
    }

    public function generateInvoice($orderData)
    {
        // dd($orderData);
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/print/invoice', $orderData);
    }

    public function generatePickup($orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/courier/generate/pickup', $orderData);
    }

    public function createExchangeOrder(array $orderData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/create/exchange', $orderData);
    }

    public function createForwordShipment($shipmentData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/shipments/create/forward-shipment', $shipmentData);
    }

    public function createReturnShipment($shipmentData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/shipments/create/return-shipment', $shipmentData);
    }

    // Tracking
    public function trackShipment(string $shipmentId)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/courier/track/shipment/'.$shipmentId);
    }

    public function trackShipmentAWB(string $awb)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/courier/track/awb/'.$awb);
    }

    public function trackMultipleShipmentAWB(array $trackData)
    {
        $token = $this->authenticate();
        \Log::info('Shiprocket bulk tracking API call:', $trackData);
        $response = Http::withToken($token)
            ->asJson()
            ->post('https://apiv2.shiprocket.in/v1/external/courier/track/awbs', $trackData);
        \Log::info('Shiprocket bulk tracking response status:', ['status' => $response->status()]);

        return $response;
    }

    public function trackShipmentOrder(array $trackData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/courier/track', $trackData);
    }

    public function cancelShipment(array $awbs)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/cancel/shipment/awbs', $awbs);
    }

    public function cancelOrders(array $ids)
    {
        $token = $this->authenticate();

        // dd($ids);
        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/orders/cancel', $ids);
    }

    public function getAllOrders_old()
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/orders');
    }

    public function getAllOrders($params = [])
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/orders', $params);
    }

    public function getOrder(string $orderId)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/orders/show/'.$orderId);
    }

    public function getAllCouriers()
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/courier/courierListWithCounts');
    }

    public function serviceability(array $shipmentData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability/', $shipmentData);
    }

    // Pickup Location
    public function getAllPickup()
    {
        $token = $this->authenticate();
       // dd($token);

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/settings/company/pickup');
    }

    public function addNewPickup(array $pickupData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/settings/company/addpickup', $pickupData);
    }

    // Wallet
    public function getWalletBalance()
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/account/details/wallet-balance');
    }

    // Products
    public function getAllProducts($filters)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/products', $filters);
    }

    public function getProduct(string $productId)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/products/show/'.$productId);
    }

    public function addNewProduct(array $productData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->post('https://apiv2.shiprocket.in/v1/external/products', $productData);
    }

    // Inventory
    public function getInventory(array $filters)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/inventory', $filters);
    }

    public function updateInventory(string $productId, array $inventoryData)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->put('https://apiv2.shiprocket.in/v1/external/inventory/'.$productId.'/update', $inventoryData);
    }

    // Statement
    public function getStatement(array $filters)
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/account/details/statement', $filters);
    }

    public function getDiscrepancy()
    {
        $token = $this->authenticate();

        return Http::withToken($token)
            ->get('https://apiv2.shiprocket.in/v1/external/billing/discrepancy');
    }
}
