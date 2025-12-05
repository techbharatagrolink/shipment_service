<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\This;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->slack_url = "https://slack.com/api/chat.postMessage";
        $this->slack_token = env('SLACK_TOKEN');
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
}
