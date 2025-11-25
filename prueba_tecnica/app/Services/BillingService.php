<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Enums\InvoiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    /**
     * Clase para generar facturas apartir de un cliente y una fecha de corte y-m-d
     * @param Customer $customer El cliente a facturar.
     * @param Carbon $billingDate Fecha de corte example 2024-04-15
     * @return Collection<Invoice>|null Retorna las facturas que tenga ligadas un cliente o null si no hay lecturas del medidor.
     */
    public function generateInvoicesForCustomer(Customer $customer, Carbon $billingDate): Collection
    {
        $generatedInvoices = collect(); //colección vacia

        $meters = $customer->meters()->where('status', 'active')->get(); //Traemos todos los medidores de un cliente cuyos estados sean activo

        if (!$meters) {
            Log::warning("El cliente {$customer->id} no tiene medidores activos.");
            return $generatedInvoices;
        }
        foreach ($meters as $meter) {
            $invoice = $this->generateInvoiceForMeter($meter, $customer, $billingDate);
            if ($invoice) {
                $generatedInvoices->push($invoice);
            }
        }
        return $generatedInvoices;
    }

    private function generateInvoiceForMeter(Meter $meter, Customer $customer, Carbon $billingDate): ?Invoice
    {

        $reading = $this->getReadingForPeriod($meter, $billingDate);

        if (!$reading) {
            return null;
        }

        return DB::transaction(
            function () use ($customer, $billingDate, $reading) {
                $invoice = $this->createInvoiceHeader($customer,$reading,$billingDate);

                $total = 0;
                $total += $this->addWaterConcept($invoice,$reading);
                $total += $this->addSewerageConcept($invoice);

                $invoice->update(['total_amount' => $total]);
                \App\Jobs\SendInvoiceEmailJob::dispatch($invoice);
                return $invoice;
            }
        );
    }
    private function getReadingForPeriod(Meter $meter, Carbon $date): ?MeterReading
    {
        return $meter->meterReadings()
            ->whereYear('reading_date', $date->year)
            ->whereMonth('reading_date', $date->month)
            ->first();
    }
    private function createInvoiceHeader(Customer $customer, MeterReading $reading, Carbon $date): Invoice
    {
        $issueDate = $reading->reading_date;
        $dueDate = $issueDate->copy()->addDays(config('billing.due_days', 15));
        // Estado de la factura
        $status = $dueDate->isPast() ? InvoiceStatus::DUE : InvoiceStatus::PENDING;

        return Invoice::create([
            'invoice_number' => 'FAC-' . $date->format('Ym') . '-' . $customer->id,
            'customer_id' => $customer->id,
            'billing_period_start' => $issueDate->copy()->startOfMonth(),
            'billing_period_end' => $issueDate->copy()->endOfMonth(),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'status' => $status,
            'total_amount' => 0,
        ]);
    }
    private function addWaterConcept(Invoice $invoice, MeterReading $reading): float
    {
        $price = config('billing.concepts.water.price_per_m3');
        $subtotal = $reading->consumption_m3 * $price;
        $taxRate = config('billing.concepts.tax_rate', 0);
        $taxAmount = $taxRate * $subtotal;
        $total = $subtotal + $taxAmount;
        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'concept' => config('billing.concepts.water.name'),
            'quantity' => $reading->consumption_m3,
            'unit_price' => $price,
            'subtotal' => $subtotal,
            'tax' => $taxAmount,
            'total' => $total,
        ]);
        return $total;
    }
    private function addSewerageConcept(Invoice $invoice): float
    {
        $price = config('billing.concepts.sewerage.fixed_price');
        $taxRate = config('billing.concepts.tax_rate', 0);
        $taxAmount = $price * $taxRate;
        $total = $price + $taxAmount;

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'concept' => config('billing.concepts.sewerage.name'),
            'quantity' => 1,
            'unit_price' => $price,
            'subtotal' => $price,
            'tax' => $taxAmount,
            'total' => $total
        ]);
        return $total;
    }
}
