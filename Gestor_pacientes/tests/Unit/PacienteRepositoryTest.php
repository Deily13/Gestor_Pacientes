<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Paciente;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Repositories\PacienteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

class PacienteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PacienteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PacienteRepository();
    }

    // =========================================================
    // DATOS DE PRUEBA
    // =========================================================

    private function crearCatalogos(): array
    {
        $tipoDocumento = TipoDocumento::create(['nombre' => 'Cédula de Ciudadanía']);
        $genero        = Genero::create(['nombre' => 'Masculino']);
        $departamento  = Departamento::create(['nombre' => 'Cundinamarca']);
        $municipio     = Municipio::create([
            'nombre'          => 'Bogotá',
            'departamento_id' => $departamento->id,
        ]);

        return compact('tipoDocumento', 'genero', 'departamento', 'municipio');
    }

    private function crearPaciente(array $catalogos, array $override = []): Paciente
    {
        return Paciente::create(array_merge([
            'tipo_documento_id' => $catalogos['tipoDocumento']->id,
            'numero_documento'  => '1098765432',
            'nombre1'           => 'Maria',
            'nombre2'           => 'Carla',
            'apellido1'         => 'Pérez',
            'apellido2'         => 'García',
            'genero_id'         => $catalogos['genero']->id,
            'departamento_id'   => $catalogos['departamento']->id,
            'municipio_id'      => $catalogos['municipio']->id,
            'correo'            => 'carla.perez@example.com',
        ], $override));
    }

    // =========================================================
    // PRUEBAS findByDocument
    // =========================================================

    public function test_find_by_document_retorna_paciente_existente(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);

        $resultado = $this->repository->findByDocument('1098765432');

        $this->assertInstanceOf(Paciente::class, $resultado);
        $this->assertEquals('1098765432', $resultado->numero_documento);
    }

    public function test_find_by_document_retorna_null_si_no_existe(): void
    {
        $resultado = $this->repository->findByDocument('9999999999');

        $this->assertNull($resultado);
    }

    public function test_find_by_document_retorna_el_paciente_correcto(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);
        $this->crearPaciente($catalogos, [
            'numero_documento' => '9876543210',
            'correo'           => 'otro@example.com',
        ]);

        $resultado = $this->repository->findByDocument('1098765432');

        $this->assertEquals('carla.perez@example.com', $resultado->correo);
    }

    // =========================================================
    // PRUEBAS findByEmail
    // =========================================================

    public function test_find_by_email_retorna_paciente_existente(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);

        $resultado = $this->repository->findByEmail('juan.perez@example.com');

        $this->assertInstanceOf(Paciente::class, $resultado);
        $this->assertEquals('carla.perez@example.com', $resultado->correo);
    }

    public function test_find_by_email_retorna_null_si_no_existe(): void
    {
        $resultado = $this->repository->findByEmail('noexiste@example.com');

        $this->assertNull($resultado);
    }

    public function test_find_by_email_retorna_el_paciente_correcto(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);
        $this->crearPaciente($catalogos, [
            'numero_documento' => '9876543210',
            'correo'           => 'otro@example.com',
        ]);

        $resultado = $this->repository->findByEmail('carla.perez@example.com');

        $this->assertEquals('1098765432', $resultado->numero_documento);
    }

    // =========================================================
    // PRUEBAS getAllPaginated
    // =========================================================

    public function test_get_all_paginated_retorna_paginador(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);

        $resultado = $this->repository->getAllPaginated();

        $this->assertInstanceOf(LengthAwarePaginator::class, $resultado);
    }

    public function test_get_all_paginated_retorna_todos_los_pacientes(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);
        $this->crearPaciente($catalogos, [
            'numero_documento' => '9876543210',
            'correo'           => 'otro@example.com',
        ]);
        $this->crearPaciente($catalogos, [
            'numero_documento' => '1111111111',
            'correo'           => 'tercero@example.com',
        ]);

        $resultado = $this->repository->getAllPaginated();

        $this->assertEquals(3, $resultado->total());
    }

    public function test_get_all_paginated_respeta_el_per_page(): void
    {
        $catalogos = $this->crearCatalogos();

        foreach (range(1, 5) as $i) {
            $this->crearPaciente($catalogos, [
                'numero_documento' => "100000000{$i}",
                'correo'           => "paciente{$i}@example.com",
            ]);
        }

        $resultado = $this->repository->getAllPaginated(perPage: 2);

        $this->assertEquals(2, $resultado->perPage());
        $this->assertEquals(2, $resultado->count());
        $this->assertEquals(5, $resultado->total());
    }

    public function test_get_all_paginated_retorna_vacio_sin_pacientes(): void
    {
        $resultado = $this->repository->getAllPaginated();

        $this->assertEquals(0, $resultado->total());
        $this->assertCount(0, $resultado->items());
    }

    public function test_get_all_paginated_carga_las_relaciones(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);

        $resultado  = $this->repository->getAllPaginated();
        $paciente   = $resultado->items()[0];

        $this->assertTrue($paciente->relationLoaded('tipoDocumento'));
        $this->assertTrue($paciente->relationLoaded('genero'));
        $this->assertTrue($paciente->relationLoaded('departamento'));
        $this->assertTrue($paciente->relationLoaded('municipio'));
    }

    public function test_get_all_paginated_relaciones_tienen_datos_correctos(): void
    {
        $catalogos = $this->crearCatalogos();
        $this->crearPaciente($catalogos);

        $resultado = $this->repository->getAllPaginated();
        $paciente  = $resultado->items()[0];

        $this->assertEquals('Cédula de Ciudadanía', $paciente->tipoDocumento->nombre);
        $this->assertEquals('Masculino',            $paciente->genero->nombre);
        $this->assertEquals('Cundinamarca',         $paciente->departamento->nombre);
        $this->assertEquals('Bogotá',               $paciente->municipio->nombre);
    }
}