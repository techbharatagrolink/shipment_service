<?php

namespace App\Jobs;

use App\Jobs\SendSlackNotification;
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

    public function cleanOrderId($orderId)
    {
        if (str_ends_with($orderId, '-C')) {
            return substr($orderId, 0, -2); // remove last 2 chars
        }

        return $orderId;
    }

    // Laravel will inject the service here automatically
    public function handle(ShiprocketService $shipRocketService, Whatsapp $whatsapp): void
    {
        $this->shipRocketService = $shipRocketService;
        $this->whatsapp = $whatsapp;

        $order_id = $this->cleanOrderId($this->rawData['order_id']) ?? null;
        $sr_order_id = $this->rawData['sr_order_id'] ?? null;
        $current_status = $this->rawData['current_status'] ?? null;
        $shipment_status = $this->rawData['shipment_status'] ?? null;
        $channel_id = $this->rawData['channel_id'] ?? null;

        // dd($order_id);
        // Get latest order info from Shiprocket API
        $current_order = $this->shipRocketService->getOrder(intval($sr_order_id)) ?? null;

        $conn = DB::connection('mysql2');

        try {

            $orders = $conn->table('orders')
                ->join('order_product', 'orders.order_id', '=', 'order_product.order_id')
                ->join(
                    'shipment_shiprocket',
                    DB::raw('order_product.invoice_number COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('shipment_shiprocket.invoice_number COLLATE utf8mb4_unicode_ci')
                )
                ->where(
                    DB::raw('shipment_shiprocket.channel_order_id COLLATE utf8mb4_unicode_ci'),
                    '=',
                    $order_id
                )
                ->select('orders.*', 'order_product.*', 'shipment_shiprocket.*')
                ->first();

            if (! $orders) {
                throw new \Exception('Order not found: ' . $order_id);
            }

            $customer_phone = $orders->mobile;
            $customer_name  = $orders->fullname;
            $table_order_id = $orders->order_id;

            // WhatsApp logic (NO DB here, NO BREAK FLOW)
            try {
                if (in_array(strtolower($current_status), ['canceled', 'cancelled'])) {
                    $this->whatsapp->send(
                        $customer_phone,
                        'order_cancel_shiprocket',
                        [$customer_name, $table_order_id, 'Product unavailability']
                    );
                }

                if (strtolower($current_status) === 'delivered') {

                    $conn->table('order_product')
    ->where('invoice_number',trim($orders->invoice_number))
    ->update([
        'delivery_date' => now(),
    ]);

                    $this->whatsapp->send(
                        $customer_phone,
                        'order_updates_delivered_shiprocket',
                        [$customer_name, $table_order_id]
                    );
                }
            } catch (\Throwable $e) {
                // 🔥 Log but DO NOT stop execution
                \Log::warning('WhatsApp send failed', [
                    'order_id' => $table_order_id,
                    'status'   => $current_status,
                    'error'    => $e->getMessage(),
                ]);
            }

            // Slack notification for "Out for Delivery" status
            try {
                if (str_contains(strtoupper($current_status), 'OUT FOR DELIVERY')) {
                    SendSlackNotification::dispatch([
                        'order_id' => $table_order_id,
                        'customer_name' => $customer_name,
                        'status' => $current_status,
                        'channel' => '#order-updates',
                    ])->onQueue('high');
                }
            } catch (\Throwable $e) {
                // 🔥 Log but DO NOT stop execution
                \Log::warning('Slack notification dispatch failed', [
                    'order_id' => $table_order_id,
                    'status'   => $current_status,
                    'error'    => $e->getMessage(),
                ]);
            }

            // 🔥 Updates
            $conn->table('shipment_shiprocket')
                ->where('channel_order_id', $order_id)
                ->update([
                    'status' => $current_status,
                    'shipment_status' => $shipment_status,
                ]);

            $conn->table('order_product')
                ->where('invoice_number', $orders->invoice_number)
                ->update([
                    'status' => $current_status,
                ]);

        } catch (\Throwable $e) {

            \Log::error('Shiprocket sync failed', [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;

        } finally {
            // ✅ GUARANTEED close
            $conn->disconnect();
        }



    }
}
