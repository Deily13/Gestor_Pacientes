<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Models\Paciente;
use App\Repositories\PacienteRepository;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserController $controller;

    /** @var PacienteRepository&MockInterface */
    protected PacienteRepository $repository;

    protected int $tipoDocumentoId;
    protected int $generoId;
    protected int $departamentoId;
    protected int $municipioId;

    // -------------------------------------------------------------------------
    // SETUP
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        /** @var PacienteRepository&MockInterface $repository */
        $repository = Mockery::mock(PacienteRepository::class);
        $this->repository = $repository;
        $this->controller = new UserController($this->repository);

        $this->seedCatalogos();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function seedCatalogos(): void
    {
        $now = now();

        $this->tipoDocumentoId = DB::table('tipos_documento')->insertGetId([
            'nombre'     => 'Cédula de Ciudadanía',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->generoId = DB::table('genero')->insertGetId([
            'nombre'     => 'Masculino',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->departamentoId = DB::table('departamentos')->insertGetId([
            'nombre'     => 'Cundinamarca',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->municipioId = DB::table('municipios')->insertGetId([
            'nombre'          => 'Bogotá',
            'departamento_id' => $this->departamentoId,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }

    private function crearPaciente(array $overrides = []): Paciente
    {
        return Paciente::create(array_merge([
            'tipo_documento_id' => $this->tipoDocumentoId,
            'numero_documento'  => '1234567890',
            'nombre1'           => 'Juan',
            'nombre2'           => null,
            'apellido1'         => 'Pérez',
            'apellido2'         => null,
            'genero_id'         => $this->generoId,
            'departamento_id'   => $this->departamentoId,
            'municipio_id'      => $this->municipioId,
            'correo'            => 'juan@example.com',
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // TESTS — index()
    // -------------------------------------------------------------------------

    public function test_index_retorna_200(): void
    {
        $response = $this->controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_index_retorna_success_true(): void
    {
        $response = $this->controller->index();
        $data     = $response->getData(true);

        $this->assertTrue($data['success']);
    }

    public function test_index_retorna_lista_vacia_sin_pacientes(): void
    {
        $response = $this->controller->index();
        $data     = $response->getData(true);

        $this->assertEmpty($data['data']['data']);
    }

    public function test_index_retorna_todos_los_pacientes(): void
    {
        $this->crearPaciente(['numero_documento' => '1111111111']);
        $this->crearPaciente(['numero_documento' => '2222222222']);
        $this->crearPaciente(['numero_documento' => '3333333333']);

        $response = $this->controller->index();
        $data     = $response->getData(true);

        $this->assertCount(3, $data['data']['data']);
    }

    public function test_index_retorna_estructura_paginada(): void
    {
        $response = $this->controller->index();
        $data     = $response->getData(true);

        // Claves estándar de paginación de Laravel
        $this->assertArrayHasKey('current_page', $data['data']);
        $this->assertArrayHasKey('per_page', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertArrayHasKey('last_page', $data['data']);
        $this->assertArrayHasKey('data', $data['data']);
    }

    public function test_index_pagina_de_15_registros(): void
    {
        $response = $this->controller->index();
        $data     = $response->getData(true);

        $this->assertEquals(15, $data['data']['per_page']);
    }

    public function test_index_incluye_relacion_tipo_documento(): void
    {
        $this->crearPaciente();

        $response = $this->controller->index();
        $data     = $response->getData(true);

        $paciente = $data['data']['data'][0];
        $this->assertArrayHasKey('tipo_documento', $paciente);
    }

    public function test_index_incluye_relacion_genero(): void
    {
        $this->crearPaciente();

        $response = $this->controller->index();
        $data     = $response->getData(true);

        $paciente = $data['data']['data'][0];
        $this->assertArrayHasKey('genero', $paciente);
    }

    public function test_index_incluye_relacion_departamento(): void
    {
        $this->crearPaciente();

        $response = $this->controller->index();
        $data     = $response->getData(true);

        $paciente = $data['data']['data'][0];
        $this->assertArrayHasKey('departamento', $paciente);
    }

    public function test_index_incluye_relacion_municipio(): void
    {
        $this->crearPaciente();

        $response = $this->controller->index();
        $data     = $response->getData(true);

        $paciente = $data['data']['data'][0];
        $this->assertArrayHasKey('municipio', $paciente);
    }
}