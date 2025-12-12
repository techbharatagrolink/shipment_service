<?php

namespace App\Jobs;

use App\Services\MetaCatalogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessMetaCatalogBatch implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $products) {

    }

    /**
     * Execute the job.
     */
    public function handle(MetaCatalogService $metaCatalogService): void
    {
        Log::info("Processing Meta Catalog Batch: ", [
            'product_count' => count($this->products),
        ]);
        //dd($this->products);

        $result = $metaCatalogService->batchCreateProducts($this->products);
        Log::info("Meta Catalog Batch Response.",$result);
        Log::info("data.",$this->products);
        if($result['success']) {
            Log::info("Meta Catalog Batch Succeeded.");
        }else{
            Log::info("Meta Catalog Batch Failed.");
        }

    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Meta Catalog Batch job failed permanently: {$this->batchId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
