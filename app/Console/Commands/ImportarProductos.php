<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Productos;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class ImportarProductos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:importar {archivo : Ruta al archivo CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar productos desde un archivo CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $archivo = $this->argument('archivo');
        
        if (!file_exists($archivo)) {
            $this->error('❌ El archivo no existe: ' . $archivo);
            return 1;
        }

        $this->info('📦 Iniciando importación de productos...');
        
        try {
            $csv = Reader::createFromPath($archivo, 'r');
            $csv->setHeaderOffset(0); // Primera fila como encabezados
            
            $records = $csv->getRecords();
            $importados = 0;
            $errores = 0;
            
            foreach ($records as $record) {
                try {
                    // Validar datos requeridos
                    if (empty($record['id']) || empty($record['producto']) || empty($record['precio'])) {
                        $this->warn('⚠️ Fila omitida - datos requeridos faltantes: ' . json_encode($record));
                        $errores++;
                        continue;
                    }
                    
                    // Verificar si el producto ya existe
                    $productoExistente = Productos::find($record['id']);
                    if ($productoExistente) {
                        $this->warn('⚠️ Producto con ID ' . $record['id'] . ' ya existe, omitiendo...');
                        $errores++;
                        continue;
                    }
                    
                    // Crear el producto
                    Productos::create([
                        'id' => (int) $record['id'],
                        'clave' => $record['clave'] ?? '',
                        'producto' => $record['producto'],
                        'precio' => (float) $record['precio'],
                        'precio_iva' => (float) ($record['precio_iva'] ?? $record['precio']),
                        'clave_producto_servicio' => $record['clave_producto_servicio'] ?? '',
                        'detalles' => $record['detalles'] ?? '',
                        'indicaciones' => $record['indicaciones'] ?? '',
                        'tiempoEntrega' => $record['tiempoEntrega'] ?? '',
                        'duracion' => $record['duracion'] ?? '',
                        'descripcion' => $record['descripcion'] ?? '',
                    ]);
                    
                    $importados++;
                    $this->line('✅ Producto importado: ' . $record['producto']);
                    
                } catch (\Exception $e) {
                    $this->error('❌ Error al importar producto: ' . $e->getMessage());
                    $errores++;
                }
            }
            
            $this->info('🎉 Importación completada!');
            $this->line('📊 Resumen:');
            $this->line('- Productos importados: ' . $importados);
            $this->line('- Errores: ' . $errores);
            
            Log::info('Importación de productos completada', [
                'archivo' => $archivo,
                'importados' => $importados,
                'errores' => $errores
            ]);
            
        } catch (\Exception $e) {
            $this->error('❌ Error al procesar el archivo: ' . $e->getMessage());
            Log::error('Error en importación de productos: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
