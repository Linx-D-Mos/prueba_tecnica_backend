<?php

namespace App\Jobs;

use App\Mail\InvoiceCreatedMail;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Queueable,Dispatchable,InteractsWithQueue,SerializesModels;

    /**
     * Create a new job instance.
     */
    public $invoice;
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->invoice->load('customer');
        $email = $this->invoice->customer->email;

        try{
            Mail::to($email)->send(new InvoiceCreatedMail($this->invoice));
            Log::info("Correo de factura {$this->invoice->invoice_number} enviado a {$email}");
        }catch(\Exception $e){
            Log::error("Fallo al enviar correo de factura {$this->invoice->id}: " . $e->getMessage());
            $this->release(10);
        }
    }
}
