<?php

namespace App\Jobs;

use AllowDynamicProperties;
use App\Services\ShiprocketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

#[AllowDynamicProperties]
class SyncOrder implements ShouldQueue
{
    use Queueable;

    public $order_id;

    protected $shipRocketService;

    /**
     * Create a new job instance.
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(ShiprocketService $shipRocketService): void
    {
        $this->shiprocket = $shipRocketService;
        $order_id = $this->order_id;
        $order_data = $this->shiprocket->getOrder($order_id)->json();
        $channel_order_id = $order_data['data']['channel_order_id'];
        $shipment_id = $order_data['data']['shipments']['id'];

        $channel = $channel_order_id;
        $parts = explode('-', $channel);
        $lastPart = end($parts);
        if (!ctype_digit($lastPart)) {
            array_pop($parts);
        }
        $invoice_number = implode('-', $parts);

        $last_update_date = date('Y-m-d H:i:s',strtotime($order_data['data']['updated_at']));
        $awb = $order_data['data']['shipments']['awb'] ?? null;

        $shipments_ids['shipment_id'] = [$shipment_id];
        $label = $this->shiprocket->generateLabel($shipments_ids)->json();
        $label_url = $label['label_url'] ?? null;


        //dd($shipment_id,$label_url,$invoice_number);

        $shipment = DB::connection('mysql2')
            ->table('shipment_shiprocket')
            ->where('channel_order_id', $order_id)
            ->update(
                [
                    'status' => $order_data['data']['status'],
                    'shipment_status' => $order_data['data']['shipments']['status'] ?? null,
                    'awb_code' => $awb ?? null,
                    'courier_company_id' => $order_data['data']['shipments']['courier_id'] ?? null,
                    'courier_name' => $order_data['data']['shipments']['courier'] ?? null,
                    'updated_at' => $last_update_date,
                    'invoice_url' => $order_data['data']['shipments']['invoice_link'] ?? null,
                    'manifest_url' => $order_data['data']['shipments']['manifest_url'] ?? null,
                    'label_url' => $label_url ?? null,
                ]);

        $order_product = DB::connection('mysql2')
            ->table('order_product')
            ->where('invoice_number', $invoice_number)  // or correct field
            ->update([
                'status' => $order_data['data']['status'],
                'tracking_id' => $awb ?? null,
                'tracking_url' => $awb!='' ? "https://shiprocket.co/tracking/$awb" : null,
                'print_label' => $label_url ?? null,
            ]);

        \Log::info("db update queue: $shipment , $order_product");
        //return [$shipment,$order_product];
    }
}
