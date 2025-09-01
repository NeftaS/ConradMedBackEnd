<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Productos;
use League\Csv\Reader;

class ImportarProductos extends Command
{
    protected $signature = 'importar:productos
        {archivo : Ruta al CSV}
        {--delimiter=, : Delimitador (por defecto coma)}';

    protected $description = 'Importar productos desde CSV usando siempre el ID del archivo como ID en la base de datos';

    public function handle()
    {
        $path = $this->argument('archivo');
        $delim = (string)$this->option('delimiter');

        if (!is_file($path)) {
            $this->error('❌ No existe el archivo: '.$path);
            return 1;
        }

        $this->info('📦 Importando productos...');
        $this->line('Opciones: delimiter='.$delim);

        try {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setDelimiter($delim);
            $csv->setHeaderOffset(0);

            $headers = array_map(fn($h)=>trim(ltrim($h, "\xEF\xBB\xBF")), $csv->getHeader());

            foreach (['Clave','Id','Producto','Precio'] as $req) {
                if (!in_array($req, $headers, true)) {
                    $this->error("❌ Encabezado requerido faltante: {$req}");
                    return 1;
                }
            }

            $csv = Reader::createFromPath($path, 'r');
            $csv->setDelimiter($delim);
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            $total = iterator_count($records);
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $csv = Reader::createFromPath($path, 'r');
            $csv->setDelimiter($delim);
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            $importados=0; $actualizados=0; $omitidos=0; $errores=0;

            DB::beginTransaction();
            foreach ($records as $row) {
                $bar->advance();
                $row = array_map(fn($v)=>is_string($v)?trim($v):$v, $row);

                $money = function($v) {
                    if ($v === null || $v === '') return null;
                    $v = str_replace([' ', ','], ['', '.'], $v);
                    return $v;
                };

                $payload = [
                    'clave'                   => $row['Clave'] ?? null,
                    'producto'                => $row['Producto'] ?? null,
                    'precio'                  => $money($row['Precio'] ?? null),
                    'precio_iva'              => $money($row['PrecioIVA'] ?? ($row['Precio'] ?? null)),
                    'clave_producto_servicio' => $row['ClaveProdServ'] ?? null,
                    'detalles'                => $row['Detalles'] ?? null,
                    'indicaciones'            => $row['Indicaciones'] ?? null,
                    'tiempo_entrega'          => $row['TiempoEntrega'] ?? null,
                    'duracion'                => $row['Duracion'] ?? null,
                    'descripcion'             => $row['Descripcion'] ?? null,
                ];

                $validator = Validator::make($payload, [
                    'clave'     => ['required','string','max:100'],
                    'producto'  => ['required','string','max:255'],
                    'precio'    => ['required'],
                    'precio_iva'=> ['nullable'],
                ]);

                if ($validator->fails()) {
                    $this->warn('⚠️ Omitido (validación): '.implode('; ', $validator->errors()->all()));
                    $omitidos++; continue;
                }

                try {
                    $idCsv = (int)$row['Id'];
                    $p = Productos::find($idCsv);
                    if ($p) {
                        $p->fill($payload)->save();
                        $actualizados++;
                    } else {
                        $p = new Productos($payload);
                        $p->id = $idCsv;
                        $p->save();
                        $importados++;
                    }
                } catch (\Throwable $e) {
                    $this->error('❌ Error fila "'.$payload['producto'].'": '.$e->getMessage());
                    $errores++;
                }
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);
            $this->info('🎉 Importación completada');
            $this->line("📊 Importados: {$importados} | Actualizados: {$actualizados} | Omitidos: {$omitidos} | Errores: {$errores}");

            Log::info('Importación de productos completada', [
                'archivo'=>$path,'importados'=>$importados,'actualizados'=>$actualizados,'omitidos'=>$omitidos,'errores'=>$errores
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ Error: '.$e->getMessage());
            Log::error('Error importación productos: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return 1;
        }

        return 0;
    }
}