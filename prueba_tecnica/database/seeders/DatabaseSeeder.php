<?php

namespace Database\Seeders;

use App\Imports\CustomersImport;
use App\Imports\MetersImport;
use App\Imports\MetersReadingImport;
use App\Imports\MonthlyReadingsImport;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $customers_file_path = storage_path('app\public\customers.xlsx');
        if(file_exists($customers_file_path)){
            FacadesExcel::import(new CustomersImport, $customers_file_path);
            $this->command->info('Clientes importados correctamente.');
        }else{
            $this->command->error('el archivo customers.xlsx no se encuentra en: '. $customers_file_path);
        }
        $meters_file_path = storage_path('app\public\water_meters.xlsx');
        if(file_exists($meters_file_path)){
            FacadesExcel::import(new MetersImport, $meters_file_path);
            $this->command->info('Medidores importados correctamente.');
        }else{
            $this->command->error('el archivo meters.xlsx no se encuentra en: '. $meters_file_path);
        }
        $meter_readings_file_path = storage_path('app\public\water_meter_readings.xlsx');
        if(file_exists($meter_readings_file_path)){
            FacadesExcel::import(new MonthlyReadingsImport($meter_readings_file_path),$meter_readings_file_path);
            $this->command->info('Lectura de medidores importados correctamente.');
        }else{
            $this->command->error('el archivo meters.xlsx no se encuentra en: '. $meter_readings_file_path);
        }
    }
}
