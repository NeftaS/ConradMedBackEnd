<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportarProductos extends Command
{
    protected $signature = 'importar:productos';
    protected $description = 'Importa productos desde CSV';

    public function handle()
    {
        $path = storage_path('app\productos.csv');

        if (!file_exists($path)) {
            $this->error("El archivo CSV no existe en: $path");
            return;
        }

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        $headers = $csv->getHeader();
        $this->info("Encabezados: " . implode(', ', $headers));

        foreach ($records as $record) {
            $this->info("Procesando registro: " . implode(', ', $record));

            if (DB::table('productos')->where('id',intval($record['Id']))->exists()) {
                $this->warn("ID {$record['Id']} ya existe. Saltando...");
                continue;
            }

            DB::table('productos')->insert([
                'id'                       => intval($record['Id']),
                'clave'                   => $record['Clave'] ?? null,
                'producto'                => $record['Producto'] ?? null,
                'precio'                  => floatval(str_replace([',', ''], '', $record['Precio'] ?? 0)),
                'precio_iva'              => floatval(str_replace([',', ''], '', $record['PrecioIVA'] ?? 0)),
                'clave_producto_servicio' => $record['ClaveProdServ'] ?? null,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }

        $this->info("Importación finalizada.");
    }
}
