<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessUploadToR2;
use App\Jobs\SyncOrder;
use App\Jobs\UpdateOrder;
use App\Services\CloudFlareService;
use App\Services\ShiprocketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\This;
use function Pest\Laravel\json;
use Illuminate\Support\Facades\Process;

class WebhookController extends Controller
{

    protected $shiprocket;
    protected $cloudflareservice;
    public function __construct(ShiprocketService $shiprocket, CloudFlareService $cloudflareservice)
    {
        $this->slack_url = "https://slack.com/api/chat.postMessage";
        $this->slack_token = env('SLACK_TOKEN');
        $this->shiprocket = $shiprocket;
        $this->cloudflareservice = $cloudflareservice;
    }

    public function webhook(Request $request){
        $rawBody = json_decode($request->getContent(), true);
        UpdateOrder::dispatch($rawBody)->onQueue('high');
        return response()->json(['message' => 'OK'], 200);
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


    public function syncAllOrder(){
        $all_orders = DB::connection('mysql2')->table('shipment_shiprocket')->get('order_id');
        foreach($all_orders as $order){
            SyncOrder::dispatch($order->order_id)->onQueue('test23');
        }
        return json_encode(['success' => true, 'message' => 'Orders have been queued for synced']);
    }

    /**
     * @throws \Exception
     */
    public function uploadToR2(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf|max:10240',
            'order_id' => 'required|string',
        ]);

        $file = $request->file('invoice');
        $order_id = $request->input('order_id');

        $filename = preg_replace(
            '/[^A-Za-z0-9.\-_]/',
            '_',
            $file->getClientOriginalName()
        );

        // ✅ Store file permanently (queue-safe)
        $storedPath = $file->storeAs(
            'r2-temp',
            uniqid() . '_' . $filename
        );
        // example: r2-temp/65a1c9e_INV-2526.pdf

        ProcessUploadToR2::dispatch(
            $storedPath,
            $filename,
            $order_id,
        )->onQueue('low');

        return response()->json([
            'success' => true,
            'message' => 'File queued for R2 upload',
        ]);
    }

   public function test()
{
    // Use PHP_BINARY to get the path to the current PHP version
    // Use base_path('artisan') to get the absolute path to your artisan file
    $result = Process::run(PHP_BINARY . ' ' . base_path('artisan') . ' octane:stop');
    
    return [
        'output'    => $result->output(),
        'error'     => $result->errorOutput(),
        'exit_code' => $result->exitCode(),
    ];
}

}
