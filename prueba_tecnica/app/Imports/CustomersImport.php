<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, SkipsOnFailure
{
    use SkipsFailures; //Para guardar los errores de manera predefinida
    public function model(array $row)
    {
        return new Customer([
            'id' => $row['id'],
            'name' => $row['name'],
            'last_name' => $row['last_name'],
            'identification_number' => (string) $row['identification_number'],
            'email' => $row['email'],
            'address' => $row['address'],
            'created_at' => $row['created_at'] ?? now(), //Dado el caso que el excel no cuente con created_at el sistema mismo lo coloca
        ]);
    }
    public function rules(): array
    {
        return [
            'id' => 'required|integer|unique:customers,id',
            'name' => 'required|string',
            'last_name' => 'required|string',
            'identification_number' => 'required|string|unique:customers,identification_number',
            'email' => 'required|email|unique:customers,email',
        ];
    }
    public function prepareForValidation($data, $index)
    {
        $data['identification_number'] = (string) ($data['identification_number'] ?? '');
        $data['email'] = trim($data['email'] ?? '');

        return $data;
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Fallo en importación de cliente', [
                'fila' => $failure->row(),
                'columna' => $failure->attribute(),
                'errores' => $failure->errors(),
                'valores' => $failure->values(),
            ]);
        }
    }
}
