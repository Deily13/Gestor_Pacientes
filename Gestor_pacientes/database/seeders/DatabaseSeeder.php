<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        \App\Models\User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@admin.com',
            'password' => bcrypt('1234567890'),
        ]);

        $cc = \App\Models\TipoDocumento::create(['nombre' => 'Cédula de Ciudadanía']);
        $ti = \App\Models\TipoDocumento::create(['nombre' => 'Tarjeta de Identidad']);

        $masc = \App\Models\Genero::create(['nombre' => 'Masculino']);
        $fem  = \App\Models\Genero::create(['nombre' => 'Femenino']);
        $nobi = \App\Models\Genero::create(['nombre' => 'No Binario']);

        $data = [
            ['id' => 1, 'nombre' => 'Huila',          'municipios' => ['Neiva', 'Baraya']],
            ['id' => 2, 'nombre' => 'Antioquia',       'municipios' => ['Medellín', 'Envigado']],
            ['id' => 3, 'nombre' => 'Cundinamarca',    'municipios' => ['Bogotá', 'Soacha']],
            ['id' => 4, 'nombre' => 'Valle del Cauca', 'municipios' => ['Cali', 'Palmira']],
            ['id' => 5, 'nombre' => 'Atlántico',       'municipios' => ['Barranquilla', 'Soledad']],
        ];

        foreach ($data as $depData) {
            $dep = \App\Models\Departamento::create([
                'id'     => $depData['id'],
                'nombre' => $depData['nombre'],
            ]);
            foreach ($depData['municipios'] as $muniNombre) {
                \App\Models\Municipio::create([
                    'departamento_id' => $dep->id,
                    'nombre'          => $muniNombre,
                ]);
            }
        }

        $fotos = [
            'https://i.pinimg.com/webp/1200x/cb/5c/0f/cb5c0f169550a8ec2319f15d87158b36.webp',
            'https://i.pinimg.com/webp/736x/97/79/42/9779429e5a62440e5c80fa318d017686.webp',
            'https://i.pinimg.com/736x/4e/b2/66/4eb2661c2fcc9b3c0f22131cf804022e.jpg',
            'https://i.pinimg.com/1200x/07/7c/f1/077cf14a54103ac2829569237f3337f3.jpg',
            'https://i.pinimg.com/736x/6c/f1/3e/6cf13e067d33b9b70cb35f7a872330a4.jpg',
        ];

        for ($i = 1; $i <= 5; $i++) {
            \App\Models\Paciente::create([
                'tipo_documento_id' => $cc->id,
                'numero_documento'  => '1000' . $i,
                'nombre1'           => 'Paciente',
                'nombre2'           => 'Prueba',
                'apellido1'         => 'Apellido',
                'apellido2'         => $i,
                'genero_id'         => $masc->id,
                'departamento_id'   => 1,
                'municipio_id'      => 2,
                'correo'            => "paciente$i@ejemplo.com",
                'foto_url'          => $fotos[$i - 1],
            ]);
        }
    }
}