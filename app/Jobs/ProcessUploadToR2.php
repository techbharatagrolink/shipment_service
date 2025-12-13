<?php
namespace App\Jobs;

use App\Services\CloudFlareService;
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

    public function handle(CloudFlareService $cloudFlareService): void
    {
        $localPath = Storage::path($this->storedPath);

        $result = $cloudFlareService->upload(
            $localPath,
            'invoices/' . $this->filename
        );

        DB::connection('mysql2')->table('orders')
            ->where('order_id', $this->order_id)
            ->update([
                'invoice_pdf' => $result
            ]);

        // ✅ cleanup temp file
        Storage::delete($this->storedPath);
    }
}
