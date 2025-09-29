<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Orden;
use Carbon\Carbon;

class OrdenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ordenes = [
            [
                'Orden_NombreMedico' => 'Dr. Juan Carlos Pérez',
                'Orden_Cedula' => '12345678',
                'Orden_NombrePaciente' => 'María González López',
                'Orden_Celular' => '9981234567',
                'Orden_Correo' => 'maria.gonzalez@email.com',
                'Orden_Fecha' => Carbon::now()->addDays(2),
                'Orden_Diagnostico' => 'Hipertensión arterial',
                'Orden_Tipo' => 'Consulta médica',
                'Orden_Descripcion' => 'Paciente presenta presión arterial elevada, requiere monitoreo y ajuste de medicación',
                'Orden_Observaciones' => 'Paciente con antecedentes familiares de hipertensión',
                'Orden_NivelUrgencia' => 'MEDIA'
            ],
            [
                'Orden_NombreMedico' => 'Dra. Ana María Rodríguez',
                'Orden_Cedula' => '87654321',
                'Orden_NombrePaciente' => 'Carlos Mendoza Ruiz',
                'Orden_Celular' => '9987654321',
                'Orden_Correo' => 'carlos.mendoza@email.com',
                'Orden_Fecha' => Carbon::now()->addDays(1),
                'Orden_Diagnostico' => 'Diabetes tipo 2',
                'Orden_Tipo' => 'Control rutinario',
                'Orden_Descripcion' => 'Control de glucosa y ajuste de medicación para diabetes',
                'Orden_Observaciones' => 'Paciente con buen control de la enfermedad',
                'Orden_NivelUrgencia' => 'BAJA'
            ],
            [
                'Orden_NombreMedico' => 'Dr. Roberto Silva',
                'Orden_Cedula' => '11223344',
                'Orden_NombrePaciente' => 'Laura Fernández Castro',
                'Orden_Celular' => '9981122334',
                'Orden_Correo' => 'laura.fernandez@email.com',
                'Orden_Fecha' => Carbon::now(),
                'Orden_Diagnostico' => 'Dolor torácico agudo',
                'Orden_Tipo' => 'Emergencia',
                'Orden_Descripcion' => 'Paciente con dolor torácico intenso, requiere evaluación inmediata',
                'Orden_Observaciones' => 'Posible angina de pecho, requiere ECG urgente',
                'Orden_NivelUrgencia' => 'CRITICA'
            ],
            [
                'Orden_NombreMedico' => 'Dra. Patricia Morales',
                'Orden_Cedula' => '55667788',
                'Orden_NombrePaciente' => 'Miguel Torres Vega',
                'Orden_Celular' => '9985566778',
                'Orden_Correo' => 'miguel.torres@email.com',
                'Orden_Fecha' => Carbon::now()->addDays(3),
                'Orden_Diagnostico' => 'Artritis reumatoide',
                'Orden_Tipo' => 'Seguimiento',
                'Orden_Descripcion' => 'Control de evolución de la artritis y ajuste de tratamiento',
                'Orden_Observaciones' => 'Paciente responde bien al tratamiento actual',
                'Orden_NivelUrgencia' => 'BAJA'
            ],
            [
                'Orden_NombreMedico' => 'Dr. Fernando Herrera',
                'Orden_Cedula' => '99887766',
                'Orden_NombrePaciente' => 'Sofía Ramírez Jiménez',
                'Orden_Celular' => '9989988776',
                'Orden_Correo' => 'sofia.ramirez@email.com',
                'Orden_Fecha' => Carbon::now()->addDays(1),
                'Orden_Diagnostico' => 'Infección respiratoria',
                'Orden_Tipo' => 'Consulta urgente',
                'Orden_Descripcion' => 'Paciente con síntomas de infección respiratoria alta',
                'Orden_Observaciones' => 'Fiebre de 38.5°C, tos seca, malestar general',
                'Orden_NivelUrgencia' => 'ALTA'
            ]
        ];

        foreach ($ordenes as $orden) {
            Orden::create($orden);
        }

        $this->command->info('Se han creado ' . count($ordenes) . ' órdenes de ejemplo.');
    }
}
