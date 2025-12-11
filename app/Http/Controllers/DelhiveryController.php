<?php

namespace App\Http\Controllers;

use App\Services\DelhiveryService;
use Illuminate\Http\Request;

class DelhiveryController extends Controller
{
    public DelhiveryService $delhiveryService;

    public function __construct(DelhiveryService $delhiveryService)
    {
        $this->delhiveryService = $delhiveryService;
    }

    // ==========================================
    // ORDER/SHIPMENT MANAGEMENT
    // ==========================================

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'shipments' => 'required|array|min:1',
            'shipments.*.name' => 'required|string|max:255',
            'shipments.*.add' => 'required|string|max:255',
            'shipments.*.pin' => 'required|string|regex:/^[0-9]{6}$/',
            'shipments.*.city' => 'required|string|max:100',
            'shipments.*.state' => 'required|string|max:100',
            'shipments.*.country' => 'required|string|max:100',
            'shipments.*.phone' => 'required|string|regex:/^[0-9]{10}$/',
            'shipments.*.order' => 'required|string|max:100',
            'shipments.*.payment_mode' => 'required|string|in:Prepaid,COD,Pickup',
            'shipments.*.return_pin' => 'nullable|string|regex:/^[0-9]{6}$/',
            'shipments.*.return_city' => 'nullable|string|max:100',
            'shipments.*.return_phone' => 'nullable|string|regex:/^[0-9]{10}$/',
            'shipments.*.return_add' => 'nullable|string|max:255',
            'shipments.*.return_state' => 'nullable|string|max:100',
            'shipments.*.return_country' => 'nullable|string|max:100',
            'shipments.*.products_desc' => 'nullable|string|max:500',
            'shipments.*.hsn_code' => 'nullable|string|max:50',
            'shipments.*.cod_amount' => 'nullable|numeric|min:0',
            'shipments.*.order_date' => 'nullable|date',
            'shipments.*.total_amount' => 'nullable|numeric|min:0',
            'shipments.*.seller_add' => 'nullable|string|max:255',
            'shipments.*.seller_name' => 'nullable|string|max:100',
            'shipments.*.seller_inv' => 'nullable|string|max:100',
            'shipments.*.quantity' => 'nullable|integer|min:1',
            'shipments.*.waybill' => 'nullable|string|max:50',
            'shipments.*.shipment_width' => 'nullable|numeric|min:0.1',
            'shipments.*.shipment_height' => 'nullable|numeric|min:0.1',
            'shipments.*.weight' => 'required|numeric|min:0.01',
            'shipments.*.seller_gst_tin' => 'nullable|string|max:15',
            'shipments.*.shipping_mode' => 'nullable|string|in:Surface,Express',
            'shipments.*.address_type' => 'nullable|string|in:home,office',
        ]);

        $response = $this->delhiveryService->createOrder($validated);

        if ($response->successful()) {
            return response()->json($response->json(), 201);
        }

        return response()->json(['error' => 'Failed to create order', 'details' => $response->json()], 500);
    }

    public function editShipment(Request $request, $waybill)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'add' => 'nullable|string|max:255',
            'pin' => 'nullable|string|regex:/^[0-9]{6}$/',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|regex:/^[0-9]{10}$/',
            'payment_mode' => 'nullable|string|in:Prepaid,COD,Pickup',
        ]);

        $response = $this->delhiveryService->editShipment($waybill, $validated);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to edit shipment', 'details' => $response->json()], 500);
    }

    public function cancelShipment($waybill)
    {
        $response = $this->delhiveryService->cancelShipment($waybill);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to cancel shipment', 'details' => $response->json()], 500);
    }

    // ==========================================
    // TRACKING
    // ==========================================

    public function trackShipment($waybill)
    {
        $response = $this->delhiveryService->trackShipment($waybill);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to track shipment', 'details' => $response->json()], 500);
    }

    public function trackMultipleShipments(Request $request)
    {
        $validated = $request->validate([
            'waybills' => 'required|array|min:1|max:50',
            'waybills.*' => 'required|string',
        ]);

        $response = $this->delhiveryService->trackMultipleShipments($validated['waybills']);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to track shipments', 'details' => $response->json()], 500);
    }

    // ==========================================
    // LABELS & DOCUMENTS
    // ==========================================

    public function generateLabel($waybill)
    {
        $response = $this->delhiveryService->generateLabel($waybill);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'label_url' => $response->body(),
            ], 200);
        }

        return response()->json(['error' => 'Failed to generate label', 'details' => $response->json()], 500);
    }

    public function fetchWaybill($clientOrderId)
    {
        $response = $this->delhiveryService->fetchWaybill($clientOrderId);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to fetch waybill', 'details' => $response->json()], 500);
    }

    // ==========================================
    // PICKUP REQUESTS
    // ==========================================

    public function createPickupRequest(Request $request)
    {
        $validated = $request->validate([
            'pickup_location' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'add' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'pin_code' => 'required|string|regex:/^[0-9]{6}$/',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'email' => 'nullable|email',
            'return_address' => 'nullable|string|max:255',
        ]);

        $response = $this->delhiveryService->createPickupRequest($validated);

        if ($response->successful()) {
            return response()->json($response->json(), 201);
        }

        return response()->json(['error' => 'Failed to create pickup request', 'details' => $response->json()], 500);
    }

    // ==========================================
    // SERVICEABILITY & COST
    // ==========================================

    public function checkServiceability(Request $request)
    {
        $validated = $request->validate([
            'from_pincode' => 'required|string|regex:/^[0-9]{6}$/',
            'to_pincode' => 'required|string|regex:/^[0-9]{6}$/',
            'payment_mode' => 'nullable|string|in:Prepaid,COD,Pickup',
            'weight' => 'nullable|numeric|min:0.01',
        ]);

        $response = $this->delhiveryService->checkServiceability(
            $validated['from_pincode'],
            $validated['to_pincode'],
            $validated['payment_mode'] ?? 'Prepaid',
            $validated['weight'] ?? 0.5
        );

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to check serviceability', 'details' => $response->json()], 500);
    }

    public function calculateShippingCost(Request $request)
    {
        $validated = $request->validate([
            'md' => 'required|string|in:Prepaid,COD',
            'ss' => 'required|string',
            'o_pin' => 'required|string|regex:/^[0-9]{6}$/',
            'd_pin' => 'required|string|regex:/^[0-9]{6}$/',
            'cgm' => 'required|numeric|min:0.01',
        ]);

        $response = $this->delhiveryService->calculateShippingCost($validated);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to calculate shipping cost', 'details' => $response->json()], 500);
    }

    public function pincodeServiceability(Request $request)
    {
        $validated = $request->validate([
            'pincode' => 'required|string|regex:/^[0-9]{6}$/',
        ]);

        $response = $this->delhiveryService->pincodeServiceability($validated['pincode']);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to check pincode serviceability', 'details' => $response->json()], 500);
    }

    // ==========================================
    // NDR MANAGEMENT
    // ==========================================

    public function updateNDRShipment(Request $request, $waybill)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:re_attempt,rto',
            'remarks' => 'nullable|string|max:500',
        ]);

        $response = $this->delhiveryService->updateNDRShipment($waybill, $validated);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['error' => 'Failed to update NDR shipment', 'details' => $response->json()], 500);
    }
}
