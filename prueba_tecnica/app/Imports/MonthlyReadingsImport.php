<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MonthlyReadingsImport implements WithMultipleSheets
{
    /**
     * @param Collection $collection
     */
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }
    public function sheets(): array
    {
        $sheets = [];

        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $sheetNames = $reader->listWorksheetNames($this->filePath);

        foreach($sheetNames as $name){
            $sheets[$name] = new MetersReadingImport;
        }

        return $sheets;
    }
}
