<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDelhiveryWebhook;

class DelhiveryWebhookController extends Controller
{


    public function handle(Request $request)
    {
        $payload = $request->all();

        // Log payload for debugging
        Log::info('Delhivery Webhook Received', $payload);

        // Dispatch job for processing
        ProcessDelhiveryWebhook::dispatch($payload)->onQueue('high');



        // Respond fast (Delhivery requires 200)
        return response()->json(['message' => 'OK'], 200);
    }
}
