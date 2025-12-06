<?php

namespace App\Jobs;

use App\Services\ShiprocketService;
use App\Services\Whatsapp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rawData;

    protected $shipRocketService;
    protected $whatsapp;

    public function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    function cleanOrderId($orderId)
    {
        if (str_ends_with($orderId, '-C')) {
            return substr($orderId, 0, -2); // remove last 2 chars
        }

        return $orderId;
    }

    // Laravel will inject the service here automatically
    public function handle(ShiprocketService $shipRocketService,Whatsapp $whatsapp): void
    {
        $this->shipRocketService = $shipRocketService;
        $this->whatsapp = $whatsapp;

        $order_id     = $this->cleanOrderId($this->rawData['order_id'])?? null;
        $sr_order_id     = $this->rawData['sr_order_id'] ?? null;
        $current_status  = $this->rawData['current_status'] ?? null;
        $shipment_status  = $this->rawData['shipment_status'] ?? null;
        $channel_id      = $this->rawData['channel_id'] ?? null;

        //dd($order_id);
        // Get latest order info from Shiprocket API
        $current_order = $this->shipRocketService->getOrder(intval($sr_order_id)) ?? null;

        $orders = DB::connection('mysql2')
            ->table('orders')
            ->join('order_product', 'orders.order_id', '=', 'order_product.order_id')
            ->join('shipment_shiprocket', DB::raw("order_product.invoice_number COLLATE utf8mb4_unicode_ci"), '=', DB::raw("shipment_shiprocket.invoice_number COLLATE utf8mb4_unicode_ci"))
            ->where(DB::raw("shipment_shiprocket.channel_order_id COLLATE utf8mb4_unicode_ci"), '=', $order_id)
            ->select('orders.*', 'order_product.*', 'shipment_shiprocket.*')
            ->first();


        //dd($orders);

        $customer_phone = $orders->mobile;
        $customer_name = $orders->fullname;
        $table_order_id = $orders->order_id;

        $templet_params = [$customer_name,$table_order_id,'Product unavailability'];

        if ($current_status=='CANCELED'){
            $whatsapp_response = $this->whatsapp->send('7898244625','order_cancel_shiprocket',$templet_params);
        }





        \Log::info('Shiprocket Order:', [
            'order' => $current_order,
            'whatsapp_response' => $whatsapp_response ?? null,
        ]);


        // update

        $shipment = DB::connection('mysql2')
            ->table('shipment_shiprocket')
            ->where('channel_order_id', $order_id)
            ->update(['status' => $current_status, 'shipment_status' => $shipment_status]);

        $order_product = DB::connection('mysql2')
            ->table('order_product')
            ->where('invoice_number', $orders->invoice_number)  // or correct field
            ->update(['status' => $current_status]);

        dump($order_product,$shipment);
    }
}
