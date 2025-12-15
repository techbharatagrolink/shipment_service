<?php

namespace App\Jobs;

use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateInvoiceAndUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            // Generate PDF content
            $pdfContent = $invoiceService->generatePdfString($this->orderId);

            if (!$pdfContent) {
                Log::error("Failed to generate PDF for order: {$this->orderId}");
                return;
            }

            // Save to temporary file
            $filename = "invoice_{$this->orderId}.pdf";
            $tempPath = 'r2-temp/' . uniqid() . '_' . $filename;
            
            Storage::put($tempPath, $pdfContent);

            // Chain the upload job
            // Using the existing ProcessUploadToR2 job
            ProcessUploadToR2::dispatch(
                $tempPath,
                $filename,
                $this->orderId
            )->onQueue('low');

            Log::info("Queued R2 upload for order: {$this->orderId}");

        } catch (\Exception $e) {
            Log::error("Error in GenerateInvoiceAndUploadJob for order {$this->orderId}: " . $e->getMessage());
            throw $e;
        }
    }
}
