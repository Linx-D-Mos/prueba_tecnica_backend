<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'billing_period_start' => $this->billing_period_start,
            'billing_period_end' => $this->billing_period_end,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'details' => InvoiceDetailResource::collection($this->invoiceDetails),
        ];
    }
}
