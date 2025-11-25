<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceGeneratorReadingRequest;
use App\Http\Requests\StoreInvoiceGeneratorRequest;
use App\Models\Customer;
use App\Services\BillingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class InvoiceGeneratorController extends Controller
{
    public function store(StoreInvoiceGeneratorRequest $request, BillingService $billingService)
    {
        $validated = $request->validated();
        $customer = Customer::findOrFail($validated['customer_id']);
        $date = Carbon::parse($validated['billing_date']);

        try {
            $invoices = $billingService->generateInvoicesForCustomer($customer,$date);
            if(!$invoices){
                return response()->json([
                    'message' => 'No se generaron factura. Verifique que el cliente tenga un medidor activo y lecturas registradas para esa fecha'
                ],422);
            }
            return response()->json([
                'message' => "Se generaron {$invoices->count()} Facturas con exito",
                'data' => $invoices
            ],201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al momento de realizar las facturas: ' . $e->getMessage()
            ],500);
        }
    }
}
