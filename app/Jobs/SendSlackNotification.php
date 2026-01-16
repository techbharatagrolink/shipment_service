<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSlackNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderData;
    protected $slackUrl;
    protected $slackToken;

    public function __construct($orderData)
    {
        $this->orderData = $orderData;
        $this->slackUrl = config('services.slack.webhook_url', 'https://slack.com/api/chat.postMessage');
        $this->slackToken = env('SLACK_TOKEN');
    }

    public function handle(): void
    {
        try {
            $orderId = $this->orderData['order_id'] ?? 'N/A';
            $customerName = $this->orderData['customer_name'] ?? 'N/A';
            $status = $this->orderData['status'] ?? 'N/A';
            $channel = $this->orderData['channel'] ?? '#order-updates';

            $message = "🚚 *Order Out for Delivery*\n\n" .
                      "Order ID: `{$orderId}`\n" .
                      "Customer: {$customerName}\n" .
                      "Status: {$status}\n" .
                      "Time: " . now()->format('Y-m-d H:i:s');

            $response = Http::withToken($this->slackToken)
                ->post($this->slackUrl, [
                    'channel' => $channel,
                    'text' => $message,
                ]);

            if (!$response->successful()) {
                Log::warning('Slack notification failed', [
                    'order_id' => $orderId,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Slack notification error', [
                'order_id' => $this->orderData['order_id'] ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
            // Don't throw - we don't want to fail the webhook processing
        }
    }
}
