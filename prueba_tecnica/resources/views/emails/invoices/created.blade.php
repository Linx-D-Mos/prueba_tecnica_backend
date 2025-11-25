<x-mail::message>
#Hola, Cliente {{ $invoice->customer_id}}
Tú factura correspondiente al periodo {{$invoice->billing_period_start}} Ya ha sido generada.

**Total a Pagar:** ${{number_format($invoice->total_amount,2)}}
**Fecha Límite:** {{$invoice->due_date}}

<x-mail::button :url="url('/api/invoices/' .$invoice->id)">
Ver factura
</x-mail::button>

Gracias, <br>
{{config('app.name')}}
</x-mail::message>
