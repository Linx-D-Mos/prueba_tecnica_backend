<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\BillingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRetroactiveInvoices extends Command
{
    protected $signature = 'invoices:generate-retroactive';

    protected $description = 'Genera facturas masivas para los meses de Agosto, Septiembre y Octubre 2024';

    public function handle(BillingService $billingService)
    {
        $this->info('Iniciando proceso de facturación retroactiva');
        $datesToProcess = [
            Carbon::create(2024, 8, 15),
            Carbon::create(2024, 9, 15),
            Carbon::create(2024, 10, 15),
        ];
        $customers = Customer::all();
        $totalOperations = $customers->count() * count($datesToProcess);
        if ($customers->isEmpty()) {
            $this->error('No hay clientes en la base de datos para facturar.');
            return;
        }
        $bar = $this->output->createProgressBar($totalOperations);
        $bar->start();

        $generatedCount = 0;
        $skippedCount = 0;
        foreach ($datesToProcess as $date) {
            foreach ($customers as $customer) {
                try {
                    $invoice = $billingService->generateInvoiceForPeriod($customer, $date);
                    if ($invoice) {
                        $generatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error generando factura cliente {$customer->id}: " . $e->getMessage());
                }
                $bar->advance();
            }
        }
        $bar->finish();
        $this->newLine(2);
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Facturas Generadas', $generatedCount],
                ['Facturas Omitidas', $skippedCount],
                ['Total Procesado', $totalOperations],
            ]
            );
            $this->info('¡Proceso finalizado correctamente!');
    }
}
