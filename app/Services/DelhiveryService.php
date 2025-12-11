<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DelhiveryService
{
    protected $token;

    protected $baseUrl;

    protected $trackingUrl;

    public function __construct()
    {
        $this->token = config('services.delhivery.api_key');
        $this->baseUrl = 'https://track.delhivery.com/api/cmu/';
        $this->trackingUrl = 'https://track.delhivery.com/api/';
    }

    // ==========================================
    // ORDER/SHIPMENT MANAGEMENT
    // ==========================================

    public function createOrder(array $orderData)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'create/', ['format' => 'json', 'data' => json_encode($orderData)]);
    }

    public function editShipment(string $waybill, array $shipmentData)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'edit/', [
            'waybill' => $waybill,
            'data' => json_encode($shipmentData),
        ]);
    }

    public function cancelShipment(string $waybill)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->post($this->baseUrl.'cancel/', [
            'waybill' => $waybill,
            'cancellation' => true,
        ]);
    }

    // ==========================================
    // TRACKING
    // ==========================================

    public function trackShipment(string $waybill)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'v1/packages/json/', [
            'waybill' => $waybill,
        ]);
    }

    public function trackMultipleShipments(array $waybills)
    {
        $waybillString = implode(',', $waybills);

        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'v1/packages/json/', [
            'waybill' => $waybillString,
        ]);
    }

    // ==========================================
    // LABELS & DOCUMENTS
    // ==========================================

    public function generateLabel(string $waybill)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'api/p/packing_slip', [
            'wbns' => $waybill,
            'pdf' => true,
        ]);
    }

    public function fetchWaybill(string $clientOrderId)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'backend/clientwarehouse/get/', [
            'cl' => $clientOrderId,
        ]);
    }

    // ==========================================
    // PICKUP REQUESTS
    // ==========================================

    public function createPickupRequest(array $pickupData)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'push/', [
            'pickup_location' => $pickupData,
        ]);
    }

    // ==========================================
    // SERVICEABILITY & COST
    // ==========================================

    public function checkServiceability(string $fromPincode, string $toPincode, string $paymentMode = 'Prepaid', float $weight = 0.5)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'api/kinko/v1/invoice/charges', [
            'md' => $paymentMode,
            'ss' => 'Delivered',
            'o_pin' => $fromPincode,
            'd_pin' => $toPincode,
            'cgm' => $weight,
        ]);
    }

    public function calculateShippingCost(array $shipmentData)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
            'Content-Type' => 'application/json',
        ])->get($this->trackingUrl.'api/kinko/v1/invoice/charges', $shipmentData);
    }

    // ==========================================
    // NDR MANAGEMENT
    // ==========================================

    public function updateNDRShipment(string $waybill, array $ndrData)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'update/', [
            'waybill' => $waybill,
            'data' => json_encode($ndrData),
        ]);
    }

    // ==========================================
    // PINCODE SERVICEABILITY (Enhanced)
    // ==========================================

    public function pincodeServiceability(string $pincode)
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->token,
        ])->get($this->trackingUrl.'c/api/pin-codes/json/', [
            'filter_codes' => $pincode,
        ]);
    }
}
