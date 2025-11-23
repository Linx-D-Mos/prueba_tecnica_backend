<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'concept' => $this->concept,
            //Casteamos a float e Int para que en el Json salga más acorde a lo estipulado en el diagrama ER
            'unit_price' => (float)$this->unit_price,
            'quantity' => (int) $this->quantity,
            'subtotal' =>  (float)$this->subtotal,
            'tax' => (float)$this->tax,
            'total' => (float)$this->total,
        ];
    }
}
