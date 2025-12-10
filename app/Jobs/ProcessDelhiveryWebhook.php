<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessDelhiveryWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $slackUrl;
    protected $slackToken;

    public function __construct($payload)
    {
        $this->payload = $payload;

        // Proper DI-style values from env/config
        $this->slackUrl   = config('services.slack.webhook_url', 'https://slack.com/api/chat.postMessage');
        $this->slackToken = env('SLACK_TOKEN');
    }

    public function handle()
    {
        // Send Slack log
        $response_slack = Http::withToken($this->slackToken)
            ->post($this->slackUrl, [
                "channel" => "#tech",
                "text"    => json_encode($this->payload),
            ]);

        Log::info("Processing Delhivery webhook", $this->payload);
        Log::info("Delhivery webhook Slack Response", [
            'slack_response' => $response_slack->json()
        ]);

        // Extract shipment data
        $shipment = $this->payload['Shipment'] ?? null;
        if (!$shipment) return;

        $orderId  = $shipment['OrderNo'] ?? null;
        $vendorId = $shipment['VendorID'] ?? null;
        $waybill  = $shipment['AWB'] ?? null;
        $invoice  = $shipment['InvoiceNo'] ?? null;

        // Status block
        $statusData = $shipment['Status'] ?? [];
        $status     = $statusData['Status'] ?? null;

        if (!$orderId || !$vendorId) {
            Log::warning("Delhivery webhook missing order/vendor id", $this->payload);
            return;
        }

        // Update DB (mysql2)
        DB::connection('mysql2')->table('shipment_delhivery')
            ->updateOrInsert(
                [
                    'order_id'  => $orderId,
                    'vendor_id' => $vendorId
                ],
                [
                    'waybill'        => $waybill,
                    'status'         => $status,
                    'invoice_number' => $invoice,
                    'tracking_url'   => $shipment['TrackingURL'] ?? null,
                    'warehouse_name' => $shipment['WarehouseName'] ?? null,
                    'updated_at'     => now()
                ]
            );

        Log::info("Delhivery shipment updated", [
            'order_id'  => $orderId,
            'vendor_id' => $vendorId,
            'status'    => $status,
            'slack_response' => $response_slack->json()
        ]);
    }
}
