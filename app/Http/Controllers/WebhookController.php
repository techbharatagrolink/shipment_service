<?php

namespace App\Http\Controllers;

use App\Jobs\SyncOrder;
use App\Jobs\UpdateOrder;
use App\Services\ShiprocketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\This;
use function Pest\Laravel\json;

class WebhookController extends Controller
{

    protected $shiprocket;
    public function __construct(ShiprocketService $shiprocket)
    {
        $this->slack_url = "https://slack.com/api/chat.postMessage";
        $this->slack_token = env('SLACK_TOKEN');
        $this->shiprocket = $shiprocket;
    }

    public function webhook(Request $request){
        $rawBody = json_decode($request->getContent(), true);
        UpdateOrder::dispatch($rawBody)->onQueue('high');
        $response = Http::withToken($this->slack_token)
            ->post($this->slack_url, [
                "channel" => "#tech",  // Your Slack channel ID
                "text" => json_encode($rawBody),
            ]);
        return $response->json();
    }

    public function syncorder($order_id){
        SyncOrder::dispatch($order_id)->onQueue('high');
        return [
            'success' => true,
            'message' => 'Order has been queued for synced',
            'order_id' => $order_id,
            'data' => null
        ];
    }
}
