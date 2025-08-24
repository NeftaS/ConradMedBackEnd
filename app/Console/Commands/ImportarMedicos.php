<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportarMedicos extends Command
{
    protected $signature = 'medicos:importar {--file=DirectorioMedicos.csv} {--delimiter=,}';
    protected $description = 'Importa médicos desde un archivo CSV';

    public function handle()
    {
        $filename = $this->option('file');
        $delimiter = $this->option('delimiter');
        $filepath = storage_path("app/{$filename}");

        if (!file_exists($filepath)) {
            $this->error("El archivo {$filename} no existe en storage/app/");
            return 1;
        }

        $this->info("Iniciando importación de médicos desde {$filename}...");

        try {
            $handle = fopen($filepath, 'r');
            if (!$handle) {
                $this->error("No se pudo abrir el archivo CSV");
                return 1;
            }

            // Remover BOM si existe
            $firstBytes = fgets($handle);
            if ($firstBytes === false) {
                $this->error("El archivo está vacío.");
                fclose($handle);
                return 1;
            }
            $firstBytes = $this->removeBom($firstBytes);

            // Volver a posicionar el puntero al inicio lógico de datos
            // Si $firstBytes contiene ya la primera línea (encabezados), la procesamos.
            $headers = str_getcsv($firstBytes, $delimiter);
            if (!$headers || count($headers) === 0) {
                $headers = fgetcsv($handle, 0, $delimiter);
            }

            if (!$headers) {
                $this->error("No se pudieron leer los encabezados del CSV");
                fclose($handle);
                return 1;
            }

            $headers = array_map([$this, 'normalizeHeader'], $headers);
            $this->info("Encabezados (normalizados): " . implode(', ', $headers));

            $importados = 0;
            $errores = 0;
            $linea = 1; // ya leímos encabezados

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $linea++;

                // Evitar filas totalmente vacías
                if ($this->rowIsEmpty($data)) {
                    continue;
                }

                try {
                    $doctorData = $this->mapearDatos($data, $headers);

                    if ($doctorData === null) {
                        // sin nombre, se ignora
                        continue;
                    }

                    // Ya existe por email o cédula
                    $doctorExistente = Doctor::query()
                        ->when(!empty($doctorData['email']), fn($q) => $q->orWhere('email', $doctorData['email']))
                        ->when(!empty($doctorData['cedula']) && $doctorData['cedula'] !== 'SIN_CEDULA', fn($q) => $q->orWhere('cedula', $doctorData['cedula']))
                        ->first();

                    if ($doctorExistente) {
                        $this->warn("Línea {$linea}: Doctor ya existe (Email: {$doctorData['email']} / Cédula: {$doctorData['cedula']})");
                        continue;
                    }

                    Doctor::create($doctorData);
                    $importados++;

                    if ($importados % 10 === 0) {
                        $this->info("Importados: {$importados} médicos...");
                    }
                } catch (\Throwable $e) {
                    $errores++;
                    $this->error("Línea {$linea}: Error - " . $e->getMessage());
                    Log::error("Error importando médico en línea {$linea}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                }
            }

            fclose($handle);

            $this->info("Importación completada.");
            $this->info("Total de médicos importados: {$importados}");
            $this->info("Total de errores: {$errores}");
            return $errores > 0 ? 1 : 0;

        } catch (\Throwable $e) {
            $this->error("Error durante la importación: " . $e->getMessage());
            Log::error("Error en importación de médicos: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }
    }

    /** --- Helpers --- */

    private function removeBom(string $line): string
    {
        if (substr($line, 0, 3) === "\xEF\xBB\xBF") {
            return substr($line, 3);
        }
        return $line;
    }

    private function normalizeHeader(?string $h): string
    {
        $h = $h ?? '';
        $h = trim($h);
        // Normalizar: quitar acentos, pasar a minúsculas, colapsar espacios
        $h = iconv('UTF-8', 'ASCII//TRANSLIT', $h);
        $h = strtolower(preg_replace('/\s+/', ' ', $h));

        // Uniformar algunos nombres conocidos
        $map = [
            'doctor'             => 'doctor',
            'correo'             => 'correo',
            'email'              => 'correo',
            'celular personal'   => 'celular personal',
            'consultorio'        => 'consultorio',
            'cedula prof.'       => 'cedula prof.',
            'cedula prof'        => 'cedula prof.',
            'cedula profesional' => 'cedula prof.',
            'especialidad'       => 'especialidad',
            'ubicacion'          => 'ubicacion',
            'ubicación'          => 'ubicacion',
            'hora visita'        => 'hora visita',
        ];

        return $map[$h] ?? $h;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function mapearDatos(array $data, array $headers): ?array
    {
        $row = [];
        foreach ($headers as $i => $h) {
            $row[$h] = $data[$i] ?? null;
        }

        // Campos posibles que nos interesan
        $nombre = trim((string)($row['doctor'] ?? ''));
        if ($nombre === '') {
            return null;
        }

        $email = trim((string)($row['correo'] ?? ''));
        $celPersonal = trim((string)($row['celular personal'] ?? ''));
        $telConsultorio = trim((string)($row['consultorio'] ?? ''));
        $cedula = trim((string)($row['cedula prof.'] ?? ''));
        $especialidad = trim((string)($row['especialidad'] ?? ''));
        // $ubicacion = trim((string)($row['ubicacion'] ?? '')); // si luego lo agregas a la DB

        // Telefono: prioriza celular personal; si no, consultorio
        $telefono = $this->limpiarTelefono($celPersonal !== '' ? $celPersonal : $telConsultorio);

        if ($email === '') {
            $email = $this->generarEmail($nombre);
        }

        // Contraseña aleatoria (mejor que fija)
        $passwordPlano = Str::password(12);
        $passwordHash = Hash::make($passwordPlano);

        $puntos = $this->calcularPuntos($especialidad);

        return [
            'nombre'   => $nombre,
            'email'    => $email,
            'telefono' => $telefono,
            'cedula'   => $cedula !== '' ? $cedula : 'SIN_CEDULA',
            'password' => $passwordHash,
            'puntos'   => (int) $puntos,
        ];
    }

    private function limpiarTelefono(?string $telefono): string
    {
        if (!$telefono) return 'SIN_TELEFONO';
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($telefono) > 10) {
            $telefono = substr($telefono, -10);
        }
        return $telefono !== '' ? $telefono : 'SIN_TELEFONO';
    }

    private function generarEmail(string $nombre): string
    {
        $nombreLimpio = mb_strtolower(trim($nombre));
        $nombreLimpio = iconv('UTF-8', 'ASCII//TRANSLIT', $nombreLimpio);
        $nombreLimpio = preg_replace('/[^a-z0-9\s\.]/', '', $nombreLimpio);
        $nombreLimpio = preg_replace('/\s+/', '.', $nombreLimpio);
        return $nombreLimpio . '@conradmed.com';
    }

    private function calcularPuntos(?string $especialidad): int
    {
        $e = mb_strtolower(trim((string)$especialidad));

        $map = [
            'cardio'         => 100,
            'onco'           => 95,
            'gineco'         => 90,
            'cirujano'       => 85,
            'pediatra'       => 85,
            'trauma'         => 80,
            'neuro'          => 75,
            'otorrino'       => 70,
            'derma'          => 65,
            'gastro'         => 60,
            'dentista'       => 60,
            'endo'           => 55,
            'urolo'          => 50,
            'quiropractico'  => 45,
            'fisioterapeuta' => 40,
            'osteopata'      => 40,
        ];

        foreach ($map as $needle => $score) {
            if (strpos($e, $needle) !== false) return $score;
        }
        return 50;
    }
}