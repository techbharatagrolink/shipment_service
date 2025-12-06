<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTask;
use App\Mail\TestMail;
use App\Models\Shipment;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ShiprocketController extends Controller
{
    protected $shiprocket;

    public function __construct(ShiprocketService $shiprocket)
    {
        $this->shiprocket = $shiprocket;
    }

    public function createOrder(Request $request)
    {
       // dd($request);
        $validated = $request->validate([
            'order_id' => 'required|string|unique:shipments,order_id',
            'order_date' => 'required|date_format:Y-m-d H:i',
            'pickup_location' => 'required|string',
            'billing_customer_name' => 'required|string',
            'billing_last_name' => 'present',
            'billing_address' => 'required|string',
            'billing_address_2' => 'nullable|string',
            'billing_city' => 'required|string',
            'billing_pincode' => 'required|integer',
            'billing_state' => 'required|string',
            'billing_country' => 'required|string',
            'billing_email' => 'required|email',
            'billing_phone' => 'required|integer',
            'shipping_is_billing' => 'required|boolean',
            // if shipping_is_billing false, validate shipping fields too
            'order_items' => 'required|array|min:1',
            'order_items.*.name' => 'required|string',
            'order_items.*.sku' => 'required|string',
            'order_items.*.units' => 'required|integer',
            'order_items.*.selling_price' => 'required|numeric',
            'order_items.*.discount' => 'nullable|numeric',
            'order_items.*.tax' => 'nullable|numeric',
            'order_items.*.hsn' => 'nullable|numeric',
            'payment_method' => 'required|string',
            'shipping_charges' => 'required|numeric',
            'giftwrap_charges' => 'required|numeric',
            'transaction_charges' => 'required|numeric',
            'total_discount' => 'required|numeric',
            'sub_total' => 'required|numeric',
            'length' => 'required|numeric',
            'breadth' => 'required|numeric',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
        ]);


        $response = $this->shiprocket->createOrder($validated);
        //dd($response->json());

        if ($response->successful()) {
            $data = $response->json();
//            $shipment = Shipment::create([
//                'order_id' => $validated['order_id'],
//                'shipment_id' => $data['shipment_id'] ?? null,
//                'courier_name' => $data['courier_name'] ?? null,
//                'awb_code' => $data['awb_code'] ?? null,
//                'status' => $data['status'] ?? null,
//                'order_data' => $validated,
//                'shiprocket_order_id' => $data['order_id'] ?? null,
//            ]);
            return response()->json($data, 201);
        }

        return response()->json(['error' => 'Failed to create shipment', 'details' => $response->json()], 500);
    }

    public function createReturnOrder(Request $request)
    {
        $validated = $request->validate([

            // ------------------------
            // ORDER BASIC DETAILS
            // ------------------------
            'order_id' => 'required|string|max:50|unique:shipments,order_id',
            'order_date' => 'required|date_format:Y-m-d',
            'channel_id' => 'nullable|integer',

            // ------------------------
            // PICKUP DETAILS
            // ------------------------
            'pickup_customer_name' => 'required|string',
            'pickup_last_name' => 'nullable|string',
            'pickup_address' => 'required|string',
            'pickup_address_2' => 'nullable|string',
            'pickup_city' => 'required|string',
            'pickup_state' => 'required|string',
            'pickup_country' => 'required|string',
            'pickup_pincode' => 'required|integer',
            'pickup_email' => 'required|email',
            'pickup_phone' => 'required|string',
            'pickup_isd_code' => 'nullable|string',

            // ------------------------
            // SHIPPING DETAILS
            // ------------------------
            'shipping_customer_name' => 'required|string',
            'shipping_last_name' => 'nullable|string',
            'shipping_address' => 'required|string',
            'shipping_address_2' => 'nullable|string',
            'shipping_city' => 'required|string',
            'shipping_country' => 'required|string',
            'shipping_pincode' => 'required|integer',
            'shipping_state' => 'required|string',
            'shipping_email' => 'nullable|email',
            'shipping_isd_code' => 'nullable|string',
            'shipping_phone' => 'required|integer',

            // ------------------------
            // ORDER ITEMS (ARRAY)
            // ------------------------
            'order_items' => 'required|array|min:1',

            'order_items.*.name' => 'required|string',
            'order_items.*.sku' => 'required|string',
            'order_items.*.units' => 'required|integer|min:1',
            'order_items.*.selling_price' => 'required|integer|min:0',
            'order_items.*.discount' => 'nullable|integer|min:0',
            'order_items.*.hsn' => 'nullable|string',

            'order_items.*.return_reason' => 'nullable|string',

            'order_items.*.qc_enable' => 'nullable|string|in:true,false',

            // CONDITIONAL VALIDATION WHEN qc_enable = true
            'order_items.*.qc_product_name' =>
                'required_if:order_items.*.qc_enable,true|string',

            'order_items.*.qc_product_image' =>
                'required_if:order_items.*.qc_enable,true|url',

            // QC additional optional fields
            'order_items.*.qc_color' => 'nullable|string|max:180',
            'order_items.*.qc_brand' => 'nullable|string|max:255',
            'order_items.*.qc_serial_no' => 'nullable|string|max:255',
            'order_items.*.qc_ean_barcode' => 'nullable|string|max:255',
            'order_items.*.qc_size' => 'nullable|string|max:180',
            'order_items.*.qc_product_imei' => 'nullable|string|max:255',

            'order_items.*.qc_brand_tag' => 'nullable|boolean',
            'order_items.*.qc_used_check' => 'nullable|boolean',
            'order_items.*.qc_sealtag_check' => 'nullable|boolean',

            'order_items.*.qc_check_damaged_product' =>
                'nullable|string|in:yes,no',

            // ------------------------
            // PAYMENT & TOTAL
            // ------------------------
            'payment_method' => 'required|string|in:Prepaid',
            'total_discount' => 'nullable|string',
            'sub_total' => 'required|integer',

            // ------------------------
            // PACKAGE DIMENSIONS
            // ------------------------
            'length' => 'required|numeric',
            'breadth' => 'required|numeric',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
        ]);

        $response = $this->shiprocket->createReturnOrder($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to create Return Order', 'details' => $response->json()], 500);
    }


    public function editReturnOrder(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string|max:50',

            // action MUST be an array, and allowed values are restricted
            'action' => 'required|array|min:1',
            'action.*' => 'required|string|in:product_details,warehouse_address',

            // CONDITIONAL: required when action includes "product_details"
            'length'  => 'required_if:action,product_details|numeric|min:0.5',
            'breadth' => 'required_if:action,product_details|numeric|min:0.5',
            'height'  => 'required_if:action,product_details|numeric|min:0.5',
            'weight'  => 'required_if:action,product_details|numeric|min:0.01',

            // CONDITIONAL: required when action includes "warehouse_address"
            'return_warehouse_id' => 'required_if:action,warehouse_address|integer',
        ]);

        $response = $this->shiprocket->editReturnOrder($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to edit Return Order', 'details' => $response->json()], 500);


    }

    public function getAllReturnOrders(Request $request)
    {
        $filter = $request->all();
        $response = $this->shiprocket->getAllReturnOrders($filter);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to get all Return Orders', 'details' => $response->json()], 500);
    }

    public function generateAWB(Request $request)
    {
        $validated = $request->validate([
           'shipment_id' => 'required|string|max:50',
           'courier_id' => 'nullable|string|max:50',
           'status' => 'nullable|string|max:50',
           'is_return' => 'required|string|max:50',
        ]);
        $response = $this->shiprocket->generateAWB($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to generate AWB', 'details' => $response->json()], 500);
    }

    public function generateManifest(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|array',
        ]);
        $response = $this->shiprocket->generateManifest($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to generate Manifest', 'details' => $response->json()], 500);
    }

    public function generateLabel(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|array',
        ]);
        $response = $this->shiprocket->generateLabel($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to generate Label', 'details' => $response->json()], 500);
    }

    public function generateInvoice(Request $request)
    {
        $ids = $request->all();
        //dd($ids);
        $response = $this->shiprocket->generateInvoice($ids);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to generate Invoice', 'details' => $response->json()], 400);
    }

    public function generatePickup(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|array',
        ]);
        $response = $this->shiprocket->generatePickup($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to generate Pickup', 'details' => $response->json()], 400);
    }

    public function createExchangeOrder(Request $request)
    {
        $validated = $request->validate([
            // --------------------------
            // BASIC EXCHANGE ORDER INFO
            // --------------------------
            'exchange_order_id'         => 'required|string|max:100',
            'seller_pickup_location_id' => 'required|string|max:100',
            'seller_shipping_location_id'=> 'required|string|max:100',
            'return_order_id'           => 'required|string|max:100',

            'order_date'                => 'required|date_format:Y-m-d',
            'payment_method'            => 'required|string|in:prepaid,Prepaid,PREPAID',

            // --------------------------
            // SHIPPING DETAILS (BUYER)
            // --------------------------
            'buyer_shipping_first_name' => 'required|string|max:150',
            'buyer_shipping_last_name'  => 'nullable|string|max:150',
            'buyer_shipping_email'      => 'required|email',
            'buyer_shipping_address'    => 'required|string|max:255',
            'buyer_shipping_address_2'  => 'nullable|string|max:255',
            'buyer_shipping_city'       => 'required|string|max:150',
            'buyer_shipping_state'      => 'required|string|max:150',
            'buyer_shipping_country'    => 'required|string|max:150',
            'buyer_shipping_pincode'    => 'required|string|max:20',
            'buyer_shipping_phone'      => 'required|string|max:20',

            // --------------------------
            // PICKUP DETAILS (BUYER)
            // --------------------------
            'buyer_pickup_first_name'   => 'required|string|max:150',
            'buyer_pickup_last_name'    => 'nullable|string|max:150',
            'buyer_pickup_email'        => 'required|email',
            'buyer_pickup_address'      => 'required|string|max:255',
            'buyer_pickup_address_2'    => 'nullable|string|max:255',
            'buyer_pickup_city'         => 'required|string|max:150',
            'buyer_pickup_state'        => 'required|string|max:150',
            'buyer_pickup_country'      => 'required|string|max:150',
            'buyer_pickup_pincode'      => 'required|string|max:20',
            'buyer_pickup_phone'        => 'required|string|max:20',

            // --------------------------
            // ORDER ITEMS ARRAY
            // --------------------------
            'order_items'               => 'required|array|min:1',

            'order_items.*.name'              => 'required|string|max:255',
            'order_items.*.selling_price'     => 'required|numeric|min:0',
            'order_items.*.units'             => 'required|integer|min:1',
            'order_items.*.hsn'               => 'nullable|string|max:50',
            'order_items.*.sku'               => 'required|string|max:150',
            'order_items.*.tax'               => 'nullable|numeric|min:0',
            'order_items.*.discount'          => 'nullable|numeric|min:0',

            'order_items.*.exchange_item_id'   => 'required|string|max:100',
            'order_items.*.exchange_item_name' => 'required|string|max:255',
            'order_items.*.exchange_item_sku'  => 'required|string|max:150',

            // --------------------------
            // PAYMENT TOTALS
            // --------------------------
            'sub_total'               => 'required|numeric|min:0',
            'shipping_charges'        => 'nullable|numeric|min:0',
            'giftwrap_charges'        => 'nullable|numeric|min:0',
            'total_discount'          => 'nullable|numeric|min:0',
            'transaction_charges'     => 'nullable|numeric|min:0',

            // --------------------------
            // RETURN PACKAGE DIMENSIONS
            // --------------------------
            'return_length' => 'required|numeric|min:0.1',
            'return_breadth'=> 'required|numeric|min:0.1',
            'return_height' => 'required|numeric|min:0.1',
            'return_weight' => 'required|numeric|min:0.01',

            // --------------------------
            // EXCHANGE PACKAGE DIMENSIONS
            // --------------------------
            'exchange_length' => 'required|numeric|min:0.1',
            'exchange_breadth'=> 'required|numeric|min:0.1',
            'exchange_height' => 'required|numeric|min:0.1',
            'exchange_weight' => 'required|numeric|min:0.01',

            // --------------------------
            // RETURN REASON
            // --------------------------
            'return_reason' => 'required|string|max:255',
        ]);

        $response = $this->shiprocket->createExchangeOrder($validated);
        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to create exchange order', 'details' => $response->json()], 500);
    }


    public function trackShipment($shipmentId)
    {
        $response = $this->shiprocket->trackShipment($shipmentId);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Tracking failed by Shipment Id', 'details' => $response->json()], 500);
    }

    public function trackShipmentAWB($awb)
    {
        $response = $this->shiprocket->trackShipmentAWB($awb);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Tracking failed by AWB', 'details' => $response->json()], 500);
    }
     public function trackMultipleShipmentAWB(Request $request)
    {  
         $trackData = $request->all();
        $response = $this->shiprocket->trackMultipleShipmentAWB($trackData);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Tracking failed by AWB', 'details' => $response->json()], 500);
     }
    public function trackShipmentOrder(Request $request)
    {
        //dd($request);
        $trackData = $request->all();

        $response = $this->shiprocket->trackShipmentOrder($trackData);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Tracking failed By Order', 'details' => $response->json()], 500);
    }
    public function cancelShipment(Request $request)
    {
        $validated = $request->validate([
           'awbs' => 'required|array|min:1',
        ]);
        $response = $this->shiprocket->cancelShipment($validated);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Cancelation failed', 'details' => $response->json()], 500);
    }

    public function cancelOrder(Request $request)
    {
        $validated = $request->validate([
            //'ids' => 'required|array|min:1',
        ]);
        //dd($request->all());
        $response = $this->shiprocket->cancelOrders($request->all());
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Cancelation failed', 'details' => $response->json()], 500);
    }
    public function getAllOrders_old()
    {
        $response = $this->shiprocket->getAllOrders();
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'no Orders Found', 'details' => $response->json()], 200);
    }
    public function getAllOrders(Request $request)
    {
    $params = $request->only([
        'page', 'per_page', 'sort', 'sort_by', 'to', 'from',
        'filter_by', 'filter', 'search', 'pickup_location',
        'channel_id', 'fbs'
    ]);
    $params = array_filter($params, function($value) {
        return $value !== null && $value !== '';
    });
    $response = $this->shiprocket->getAllOrders($params);
    if ($response->successful()) {
        return response()->json($response->json());
    }
    return response()->json(['error' => 'no Orders Found', 'details' => $response->json()], 200);
   }
    public function getOrder($orderId){
        $response = $this->shiprocket->getOrder($orderId);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'No Orders Found', 'details' => $response->json()], 200);
    }
    /**
     * @OA\Get(
     *      path="/api/shipments/couriers",
     *      operationId="getAllCouriers",
     *      tags={"Shipments"},
     *      summary="Get all couriers",
     *      description="Returns list of all available couriers",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function getAllCouriers(){
        $response = $this->shiprocket->getAllCouriers();
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'no Couriers Found', 'details' => $response->json()], 200);
    }
    public function serviceability(){
        $shipmentData = request()->all();
        $response = $this->shiprocket->serviceability($shipmentData);
        //dd($response);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'no Service ability Found', 'details' => $response->json()], 200);
    }

    public function createForwordShipment(Request $request)
    {
        $validated = $request->validate([

            // OPTIONAL FIELDS
            'mode' => 'nullable|string|in:Surface,Air',
            'request_pickup' => 'nullable|boolean',
            'print_label' => 'nullable|boolean',
            'generate_manifest' => 'nullable|boolean',
            'ewaybill_no' => 'nullable|string',
            'courier_id' => 'nullable|integer',
            'reseller_name' => 'nullable|string',
            'isd_code' => 'nullable|string',
            'billing_isd_code' => 'nullable|string',
            'channel_id' => 'nullable|string',
            'company_name' => 'nullable|string',
            'billing_alternate_phone' => 'nullable|integer',
            'shipping_last_name' => 'nullable|string',
            'shipping_address_2' => 'nullable|string',
            'shipping_country' => 'nullable|string',
            'shipping_email' => 'nullable|email',
            'shipping_phone' => 'nullable|integer',
            'shipping_pincode' => 'nullable|integer',
            'shipping_state' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_customer_name' => 'nullable|string',
            'customer_gstin' => 'nullable|string',
            'shipping_charges' => 'nullable|integer',
            'giftwrap_charges' => 'nullable|integer',
            'transaction_charges' => 'nullable|integer',
            'total_discount' => 'nullable|integer',
            'order_type' => 'nullable|string|in:ESSENTIALS,NON ESSENTIALS',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'what3words_address' => 'nullable|string',
            'is_document' => 'nullable|integer|in:0,1',

            // REQUIRED FIELDS
            'order_id' => 'required|string',
            'order_date' => 'required|string',
            'billing_customer_name' => 'required|string',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string',
            'billing_state' => 'required|string',
            'billing_country' => 'required|string',
            'billing_pincode' => 'required|integer',
            'billing_email' => 'required|email',
            'billing_phone' => 'required|integer',
            'shipping_is_billing' => 'required|boolean',
            'payment_method' => 'required|string|in:COD,Prepaid',
            'sub_total' => 'required|integer',
            'weight' => 'required',
            'length' => 'required|numeric|min:0.5',
            'breadth' => 'required|numeric|min:0.5',
            'height' => 'required|numeric|min:0.5',
            'pickup_location' => 'required|string',

            // OPTIONAL BUT PRESENT
            'billing_last_name' => 'nullable|string',
            'billing_address_2' => 'nullable|string',

            // ORDER ITEMS
            'order_items' => 'required|array|min:1',
            'order_items.*.name' => 'required|string',
            'order_items.*.sku' => 'required|string',
            'order_items.*.units' => 'required|integer',
            'order_items.*.selling_price' => 'required|integer',
            'order_items.*.hsn' => 'nullable|integer',
            'order_items.*.tax' => 'nullable|integer',
            'order_items.*.discount' => 'nullable|integer',

            // CONDITIONAL SHIPPING BLOCK
            'shipping_customer_name' => 'required_if:shipping_is_billing,false|string',
            'shipping_address' => 'required_if:shipping_is_billing,false|string',
            'shipping_city' => 'required_if:shipping_is_billing,false|string',
            'shipping_state' => 'required_if:shipping_is_billing,false|string',
            'shipping_country' => 'required_if:shipping_is_billing,false|string',
            'shipping_pincode' => 'required_if:shipping_is_billing,false|integer',
            'shipping_email' => 'required_if:shipping_is_billing,false|email',
            'shipping_phone' => 'required_if:shipping_is_billing,false|integer',

            // VENDOR DETAILS (OPTIONAL)
            'vendor_details' => 'nullable|array',

            'vendor_details.email' => 'required_with:vendor_details|email',
            'vendor_details.phone' => 'required_with:vendor_details|integer',
            'vendor_details.name' => 'required_with:vendor_details|string',
            'vendor_details.address' => 'required_with:vendor_details|string|min:10',
            'vendor_details.address_2' => 'nullable|string|min:10',
            'vendor_details.city' => 'required_with:vendor_details|string',
            'vendor_details.state' => 'required_with:vendor_details|string',
            'vendor_details.country' => 'required_with:vendor_details|string',
            'vendor_details.pin_code' => 'required_with:vendor_details|integer',
            'vendor_details.pickup_location' => 'required_with:vendor_details|string|max:36|regex:/^[A-Za-z0-9 ]+$/',

        ]);
        $response = $this->shiprocket->createForwordShipment($validated);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To create', 'details' => $response->json()], 200);
    }

    public function createReturnShipment(Request $request)
    {
        $validated = $request->validate([

            // --- BASIC ORDER INFO ---
            'order_id'               => 'required|string|max:50',
            'order_date'             => 'required|date_format:Y-m-d',
            'channel_id'             => 'nullable|integer',

            // --- PICKUP DETAILS ---
            'pickup_customer_name'   => 'required|string',
            'pickup_last_name'       => 'nullable|string',
            'company_name'           => 'nullable|string',

            'pickup_address'         => 'required|string',
            'pickup_address_2'       => 'nullable|string',
            'pickup_city'            => 'required|string',
            'pickup_state'           => 'required|string',
            'pickup_country'         => 'required|string',
            'pickup_pincode'         => 'required|integer',

            'pickup_email'           => 'required|email',
            'pickup_phone'           => 'required|string',
            'pickup_isd_code'        => 'nullable|string',

            // --- SHIPPING DETAILS ---
            'shipping_customer_name' => 'required|string',
            'shipping_last_name'     => 'nullable|string',
            'shipping_address'       => 'required|string',
            'shipping_address_2'     => 'nullable|string',
            'shipping_city'          => 'required|string',
            'shipping_state'         => 'required|string',
            'shipping_country'       => 'required|string',
            'shipping_pincode'       => 'required|integer',

            'shipping_email'         => 'required|email',
            'shipping_phone'         => 'required|integer',
            'shipping_isd_code'      => 'nullable|string',

            // --- ORDER ITEMS (ARRAY) ---
            'order_items'            => 'required|array|min:1',
            'order_items.*.name'     => 'required|string',
            'order_items.*.sku'      => 'required|string',
            'order_items.*.units'    => 'required|integer|min:1',
            'order_items.*.selling_price' => 'required|integer|min:0',
            'order_items.*.discount'       => 'nullable|integer|min:0',
            'order_items.*.hsn'            => 'nullable|string',

            // QC RULES
            'order_items.*.qc_enable'        => 'nullable|in:TRUE,FALSE,True,False,true,false',
            'order_items.*.qc_color'         => 'nullable|string|max:180',
            'order_items.*.qc_brand'         => 'nullable|string|max:255',
            'order_items.*.qc_serial_no'     => 'nullable|string|max:255',
            'order_items.*.qc_ean_barcode'   => 'nullable|string|max:255',
            'order_items.*.qc_size'          => 'nullable|string|max:180',
            'order_items.*.qc_product_imei'  => 'nullable|string|max:255',

            // CONDITIONAL FIELDS IF qc_enable = TRUE
            'order_items.*.qc_product_name'  => 'required_if:order_items.*.qc_enable,TRUE|string|max:255',
            'order_items.*.qc_product_image' => 'required_if:order_items.*.qc_enable,TRUE|url|max:255',

            // --- PAYMENT ---
            'payment_method'         => 'required|string|in:COD,Prepaid',
            'total_discount'         => 'nullable|string',
            'sub_total'              => 'required|integer|min:0',

            // --- PACKAGE DIMENSIONS ---
            'length'                 => 'required|integer|min:1',
            'breadth'                => 'required|integer|min:1',
            'height'                 => 'required|integer|min:1',
            'weight'                 => 'required|integer|min:1',

            // --- PICKUP REQUEST ---
            'request_pickup'         => 'nullable|boolean',
        ]);

        $response = $this->shiprocket->createReturnShipment($validated);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To create Return Shipment', 'details' => $response->json()], 200);
    }

    public function getAllPickup(){
        $response = $this->shiprocket->getAllPickup();
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function addNewPickup(Request $request)
    {
        $validated = $request->validate([

            // REQUIRED FIELDS
            'pickup_location' => 'required|string|max:36',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|integer',
            'address' => 'required|string|max:80',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pin_code' => 'required|integer',

            // OPTIONAL FIELDS
            'address_2' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'address_type' => 'nullable|string|in:vendor',
            'vendor_name' => 'nullable|string|required_if:address_type,vendor',
            'gstin' => 'nullable|string',
        ]);
        $response = $this->shiprocket->addNewPickup($validated);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To create', 'details' => $response->json()], 200);
    }

    public function getWalletBalance(){
        $response = $this->shiprocket->getWalletBalance();
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }
    public function getAllProducts(Request $request)
    {
        $validated = $request->validate([
            'page' => 'nullable|numeric',
            'per_page' => 'nullable|numeric',
            'sort' => 'nullable|string',
            'sort_by' => 'nullable|string',
            'filter' => 'nullable|string',
            'filter_by' => 'nullable|string',
        ]);
        $response = $this->shiprocket->getAllProducts($validated);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function getProductDetails($product_id){
        $response = $this->shiprocket->getProduct($product_id);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function addNewProduct(Request $request)
    {
        $validated = $request->validate([

            // REQUIRED FIELDS
            'sku' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|string|in:Single,Multiple',
            'qty' => 'required|integer',
            'category_code' => 'required|string',

            // OPTIONAL FIELDS
            'HSN' => 'nullable|string',
            'tax_code' => 'nullable|string',
            'low_stock' => 'nullable|string',
            'description' => 'nullable|string',
            'brand' => 'nullable|string',
            'size' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'length' => 'nullable|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'ean' => 'nullable|string|size:13',
            'upc' => 'nullable|string|size:12',
            'isbn' => 'nullable|string|size:10',
            'color' => 'nullable|string',
            'imei_serialnumber' => 'nullable|string',
            'cost_price' => 'nullable|integer',
            'mrp' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image_url' => 'nullable|string',

            // QC DETAILS ARRAY (OPTIONAL)
            'qc_details' => 'nullable|array',

            // CONDITIONAL YES FIELDS UNDER qc_details
            'qc_details.product_image' => 'required_with:qc_details|string',
            'qc_details.brand' => 'required_with:qc_details|string',
            'qc_details.brand_tag' => 'required_with:qc_details|string',
            'qc_details.color' => 'required_with:qc_details|string',
            'qc_details.size' => 'required_with:qc_details|string',
            'qc_details.product_imei' => 'required_with:qc_details|string|size:15',
            'qc_details.serial_no' => 'required_with:qc_details|string',
        ]);

        $response = $this->shiprocket->addNewProduct($validated);

        if ($response->successful()) {
            return response()->json(['message'=>'Product Created' ,'details' =>$response->json()]);
        }

        return response()->json([
            'error' => 'Failed To create',
            'details' => $response->json()
        ], 200);
    }

    public function getInventory(){
        $filterData = request()->all();
        $response = $this->shiprocket->getInventory($filterData);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function updateInventory($product_id,Request $request){
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'action' => 'required|string',
        ]);
        $response = $this->shiprocket->updateInventory($product_id,$validated);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Update', 'details' => $response->json()], 200);
    }

    public function getStatement(){
        $filterData = request()->all();
        $response = $this->shiprocket->getStatement($filterData);
        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function getDiscrepancy()
    {
        $response = $this->shiprocket->getDiscrepancy();

        if ($response->successful()) {
            return response()->json($response->json());
        }
        return response()->json(['error' => 'Failed To Fetch', 'details' => $response->json()], 200);
    }

    public function demoQueue()
    {

        ProcessTask::dispatch()->onQueue('high')->withoutDelay();
        return response()->json(['msg' => 'put to Queue'], 200);
    }

}
