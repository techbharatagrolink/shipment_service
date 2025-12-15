<?php

namespace Tests\Feature;

use App\Jobs\GenerateInvoiceAndUploadJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    /**
     * Test invoice generation queues the job
     */
    public function test_invoice_generation_queues_job()
    {
        $this->withoutMiddleware();
        Queue::fake();

        $orderId = 'ORD12345';

        $response = $this->get('/api/invoice/generate/' . $orderId);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Invoice generation queued successfully',
            'order_id' => $orderId
        ]);

        Queue::assertPushed(GenerateInvoiceAndUploadJob::class, function ($job) use ($orderId) {
            // Check if the protected property or public property matches
            // Using reflection since property might be private
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('orderId');
            $property->setAccessible(true);
            return $property->getValue($job) === $orderId;
        });
    }
}
