<?php
namespace App\Jobs;

use App\Services\CloudFlareService;
use App\Services\Whatsapp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessUploadToR2 implements ShouldQueue
{
    use Queueable;

    private string $storedPath;
    private string $filename;
    private string $order_id;

    public function __construct(string $storedPath, string $filename,string $order_id)
    {
        $this->storedPath = $storedPath;
        $this->filename = $filename;
        $this->order_id = $order_id;
    }

    public function handle(CloudFlareService $cloudFlareService,Whatsapp $whatsapp): void
    {
        $localPath = Storage::path($this->storedPath);

        $result = $cloudFlareService->upload(
            $localPath,
            'invoices/' . $this->filename
        );

        $user_data = DB::connection('mysql2')->table('orders')
            ->where('order_id', $this->order_id)->first();

        DB::connection('mysql2')->table('orders')
            ->where('order_id', $this->order_id)
            ->update([
                'invoice_pdf' => $result
            ]);

        $wp_res =$whatsapp->send($user_data->mobile,'customer_invoice',[
            'file',
            $user_data->fullname,
            $this->order_id,
            $result,
            $this->filename
        ]);

        \Log::info("wp res : $wp_res");
        // ✅ cleanup temp file
        Storage::delete($this->storedPath);
    }
}
