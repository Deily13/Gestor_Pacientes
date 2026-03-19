<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
public function run(): void
{
    
    \App\Models\User::create([
        'name' => 'Administrador',
        'email' => 'admin@admin.com',
        'password' => bcrypt('1234567890'),
    ]);

    
    $cc = \App\Models\TipoDocumento::create(['nombre' => 'Cédula de Ciudadanía']);
    $ti = \App\Models\TipoDocumento::create(['nombre' => 'Tarjeta de Identidad']);

    $masc = \App\Models\Genero::create(['nombre' => 'Masculino']);
    $fem = \App\Models\Genero::create(['nombre' => 'Femenino']);
    $nobi = \App\Models\Genero::create(['nombre' => 'No Binario']);

    $data = [
        ['id' => 1, 'nombre' => 'Huila', 'municipios' => ['Neiva', 'Baraya']],
        ['id' => 2, 'nombre' => 'Antioquia', 'municipios' => ['Medellín', 'Envigado']],
        ['id' => 3, 'nombre' => 'Cundinamarca', 'municipios' => ['Bogotá', 'Soacha']],
        ['id' => 4, 'nombre' => 'Valle del Cauca', 'municipios' => ['Cali', 'Palmira']],
        ['id' => 5, 'nombre' => 'Atlántico', 'municipios' => ['Barranquilla', 'Soledad']],
    ];

    foreach ($data as $depData) {
        $dep = \App\Models\Departamento::create([
            'id' => $depData['id'], 
            'nombre' => $depData['nombre']
        ]);

        foreach ($depData['municipios'] as $muniNombre) {
            \App\Models\Municipio::create([
                'departamento_id' => $dep->id,
                'nombre' => $muniNombre
            ]);
        }
    }

    
    for ($i = 1; $i <= 5; $i++) {
        \App\Models\Paciente::create([
            'tipo_documento_id' => $cc->id,
            'numero_documento' => '1000' . $i,
            'nombre1' => 'Paciente',
            'nombre2' => 'Prueba',
            'apellido1' => 'Apellido',
            'apellido2' => $i,
            'genero_id' => $masc->id,
            'departamento_id' => 1, 
            'municipio_id' => 2,    
            'correo' => "paciente$i@ejemplo.com"
        ]);
    }
}
}
