<?php

namespace App\Imports;

use App\Models\MeterReading;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MetersReadingImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, SkipsOnFailure
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    use SkipsFailures;
    public function model(array $row)
    {
        return new MeterReading([
            'meter_id' => $row['meter_id'],
            'reading_date' => $row['reading_date'],
            'previous_reading' => $row['previous_reading'],
            'current_reading' => $row['current_reading'],
            'consumption_m3' => $row['consumption_m3'],
            'observation' => $row['observation'],
            'created_at' => $row['created_at'] ?? now(),
        ]);
    }
    public function rules(): array
    {
        return [
            'meter_id' => 'required|integer|exists:meters,id',
            'reading_date' => 'required|date',
            'previous_reading' => 'required|numeric',
            'current_reading' => 'required|numeric',
            'consumption_m3' => 'required|numeric',
            'observation' => 'nullable|string',
        ];
    }
    public function prepareForValidation($data, $index)
    {
        $data['meter_id'] = isset($data['meter_id']) ? (int) ($data['meter_id'] ?? 0) : null;
        if (isset($data['reading_date'])  && is_numeric($data['reading_date'])) {
            $data['reading_date'] = Date::excelToDateTimeObject($data['reading_date']);
        }
        $data['previous_reading'] = isset($data['previous_reading']) ? (float)($data['previous_reading'] ?? 0.0) : 0;
        $data['current_reading'] = isset($data['current_reading']) ? (float)($data['current_reading'] ?? 0.0) : null;
        $data['consumption_m3'] = isset($data['consumption_m3']) ? (float)($data['consumption_m3']) : null;
        return $data;
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Fallo en la importación de lectura de medidor', [
                'fila' => $failure->row(),
                'columna' => $failure->attribute(),
                'errores' => $failure->errors(),
                'valores' => $failure->values(),
            ]);
        }
    }
}
