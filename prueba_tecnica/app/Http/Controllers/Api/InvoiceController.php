<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Get /api/invoices
     * Lista todas las facturas con paginación.
    */
    public function index()
    {
        $invoices = Invoice::with('invoiceDetails')
            ->latest()
            ->paginate(10);
        return InvoiceResource::collection($invoices);
    }
    /**
     * Get /api/invoices/{id}
     * Muestra una factura eespecífica.
    */
    public function show(Invoice $invoice){
        $invoice->load('invoiceDetails');
        return new InvoiceResource($invoice);
    }
}
