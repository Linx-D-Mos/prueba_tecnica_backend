<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    /**
     * Clase para generar facturas apartir de un cliente y una fecha de corte y-m-d
     * @param Customer $customer El cliente a facturar.
     * @param Carbon $billingDate Fecha de corte example 2024-04-15
     * @return Invoice|null Retorna la factura o null si no hay lecturas.
     */
    public function generateInvoiceForPeriod(Customer $customer, Carbon $billingDate): ?Invoice
    {
        $meter = $customer->meters()->where('status', 'active')->first();
        if (!$meter) {
            Log::warning("El cliente {$customer->id} no tiene medidor activo.");
            return null;
        }
        $reading =  $this->getReadingForPeriod($meter, $billingDate);
        if (!$reading) {
            return null;
        }
        return DB::transaction(function () use ($customer, $reading, $billingDate) {
            $invoice = $this->createInvoiceHeader($customer, $reading, $billingDate);
            $total = 0;
            $total += $this->addWaterConcept($invoice, $reading);
            $total += $this->addSewerageConcept($invoice);
            $invoice->update(['total_amount' => $total]);
            return $invoice;
        });
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
        $taxRate = config('billing.concepts.tax_rate',0);
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
