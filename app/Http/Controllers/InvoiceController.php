<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Generate invoice PDF for a given order ID (Async)
     */
    public function generateInvoice(Request $request, string $orderId): \Illuminate\Http\JsonResponse
    {
        // Dispatch the job to generate and upload invoice
        \App\Jobs\GenerateInvoiceAndUploadJob::dispatch($orderId)->onQueue('low');

        return response()->json([
            'success' => true,
            'message' => 'Invoice generation queued successfully',
            'order_id' => $orderId
        ]);
    }
}
