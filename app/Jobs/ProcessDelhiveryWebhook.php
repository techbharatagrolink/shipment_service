<?php

namespace App\Jobs;

use App\Services\Whatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessDelhiveryWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    protected $slackUrl;

    protected $slackToken;

    protected $whatsapp;

    public function __construct($payload)
    {
        $this->payload = $payload;

        // Proper DI-style values from env/config
        $this->slackUrl = config('services.slack.webhook_url', 'https://slack.com/api/chat.postMessage');
        $this->slackToken = env('SLACK_TOKEN');
    }

    public function handle(Whatsapp $whatsapp)
    {

// 🔹 Slack log (NO DB here)
        $response_slack = Http::withToken($this->slackToken)
            ->post($this->slackUrl, [
                'channel' => '#tech',
                'text' => json_encode($this->payload),
            ]);

        Log::info('Processing Delhivery webhook', $this->payload);
        $conn = DB::connection('mysql2');

// 🔹 Extract shipment
        $shipment = $this->payload['Shipment'] ?? null;
        if (! $shipment) {
            return;
        }

        $orderId  = $shipment['ReferenceNo'] ?? null;
        $waybill  = $shipment['AWB'] ?? null;
        $statusData = $shipment['Status'] ?? [];
        $status     = $statusData['Status'] ?? null;
        $trackingUrl = $shipment['TrackingURL'] ?? null;

        

        $vendorId = $shipment['VendorID'] ?? null;
       
        $invoice  = $shipment['InvoiceNo'] ?? null;

        

        if (! $orderId) {
            Log::warning('Delhivery webhook missing order id', $this->payload);
            return;
        }

        Log::info('Delhivery data to update', $statusData);
// 🔥 SINGLE DB CONNECTION
        

        try {


            $data_shipment_delivery = $conn->table('shipment_delhivery')
    ->where('order_id', $orderId)
    ->first();

    //dump($data_shipment_delivery);
            
           $conn->table('shipment_delhivery')
    ->where('order_id', $orderId)
    ->update([
        'waybill'        => $waybill,
        'status'         => $status,
        'tracking_url'   => $waybill!=null ? "https://www.delhivery.com/track/package/$waybill" : null,
        'updated_at'     => now(),
    ]);

         $conn->table('order_product')
    ->where('invoice_number',trim($data_shipment_delivery->invoice_number))
    ->update([
        'status'     => $status,
        'update_date' => now(),
    ]);


            // 🔹 Fetch order
            $orders = $conn->table('orders')
                ->join('order_product', 'orders.order_id', '=', 'order_product.order_id')
                ->join(
                    'shipment_delhivery',
                    DB::raw('order_product.invoice_number COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('shipment_delhivery.invoice_number COLLATE utf8mb4_unicode_ci')
                )
                ->where(
                    DB::raw('shipment_delhivery.order_id COLLATE utf8mb4_unicode_ci'),
                    '=',
                    $orderId
                )
                ->select('orders.*', 'order_product.*', 'shipment_delhivery.*')
                ->first();

            if (! $orders) {
                Log::warning('Delhivery order not found', ['order_id' => $orderId]);
                return;
            }

            // 🔹 WhatsApp notifications (NO DB)
            $customer_phone = $orders->mobile;
            $customer_name  = $orders->fullname;
            $table_order_id = $orders->order_id;



            try {
                if (strtolower($status) === 'delivered') {
                    $whatsapp->send(
                        $customer_phone,
                        'order_updates_delivered_shiprocket',
                        [$customer_name, $table_order_id]
                    );
                }

                if (strtolower($status) === 'cancelled') {
                    $whatsapp->send(
                        $customer_phone,
                        'order_updates_cancelled_shiprocket',
                        [$customer_name, $table_order_id, 'Product unavailability']
                    );
                }
            }catch (\Exception $exception){
                Log::error($exception->getMessage());
            }

            Log::info('Delhivery shipment updated', [
                'order_id' => $orderId,
                'vendor_id' => $vendorId,
                'status' => $status,
                'slack_response' => $response_slack->json(),
            ]);

        } catch (\Throwable $e) {

            Log::error('Delhivery webhook failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;

        } finally {
            // ✅ GUARANTEED CLOSE
            $conn->disconnect();
        }

    }
}
