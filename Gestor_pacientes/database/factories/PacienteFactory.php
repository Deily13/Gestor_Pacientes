<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'tipo_documento_id' => \App\Models\TipoDocumento::inRandomOrder()->first()?->id
                ?? \App\Models\TipoDocumento::factory()->create()->id,

            'numero_documento'  => $this->faker->unique()->numerify('##########'),

            'nombre1'           => $this->faker->firstName(),
            'nombre2'           => $this->faker->optional()->firstName(),

            'apellido1'         => $this->faker->lastName(),
            'apellido2'         => $this->faker->optional()->lastName(),

            'genero_id'         => \App\Models\Genero::inRandomOrder()->first()?->id
                ?? \App\Models\Genero::factory()->create()->id,

            'departamento_id'   => \App\Models\Departamento::inRandomOrder()->first()?->id
                ?? \App\Models\Departamento::factory()->create()->id,

            'municipio_id'      => \App\Models\Municipio::inRandomOrder()->first()?->id
                ?? \App\Models\Municipio::factory()->create()->id,

            'correo'            => $this->faker->optional()->safeEmail(),
        ];
    }
}