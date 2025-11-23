<?php

namespace App\Imports;

use App\Models\Meter;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MetersImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, SkipsOnFailure
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    use SkipsFailures;
    public function model(array $row)
    {
        return new Meter([
            'customer_id' => $row['customer_id'],
            'serial_number' => $row['serial_number'],
            'installation_date' => $row['installation_date'],
            'status' => $row['status'],
            'created_at' => $row['created_at'] ?? now(),
        ]);
    }
    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'serial_number' => 'required|string|unique:meters,serial_number',
            'installation_date' => 'required|date',
            'status' => 'required|in:active,inactive',
        ];
    }
    public function prepareForValidation($data, $index)
    {
        $data['customer_id'] = isset($data['customer_id']) ? (int) ($data['customer_id']  ?? 0) : null;
        $data['status'] = isset($data['status']) ? strtolower(trim($data['status'])) : null;
        if (isset($data['installation_date']) && is_numeric($data['installation_date'])) {
            $data['installation_date'] = Date::excelToDateTimeObject($data['installation_date']);
        }
        return $data;
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Fallo en la importación de medidor', [
                'fila' => $failure->row(),
                'columna' => $failure->attribute(),
                'errores' => $failure->errors(),
                'valores' => $failure->values(),
            ]);
        }
    }
}
