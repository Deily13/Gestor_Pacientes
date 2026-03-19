<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Paciente;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PacienteModelTest extends TestCase
{
    use RefreshDatabase;

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

    private function datosPaciente(array $catalogos): array
    {
        return [
            'tipo_documento_id' => $catalogos['tipoDocumento']->id,
            'numero_documento'  => '1098765432',
            'nombre1'           => 'Juan',
            'nombre2'           => 'Carlos',
            'apellido1'         => 'Pérez',
            'apellido2'         => 'García',
            'genero_id'         => $catalogos['genero']->id,
            'departamento_id'   => $catalogos['departamento']->id,
            'municipio_id'      => $catalogos['municipio']->id,
            'correo'            => 'juan.perez@example.com',
        ];
    }

    // =========================================================
    // PRUEBAS DE TABLA Y ATRIBUTOS
    // =========================================================

   
    public function test_el_modelo_usa_la_tabla_correcta(): void
    {
        $paciente = new Paciente();

        $this->assertEquals('paciente', $paciente->getTable());
    }

    public function test_el_modelo_tiene_los_fillable_correctos(): void
    {
        $paciente = new Paciente();

        $fillableEsperado = [
            'tipo_documento_id',
            'numero_documento',
            'nombre1',
            'nombre2',
            'apellido1',
            'apellido2',
            'genero_id',
            'departamento_id',
            'municipio_id',
            'correo',
        ];

        $this->assertEquals($fillableEsperado, $paciente->getFillable());
    }

    // =========================================================
    // PRUEBAS DE CREACIÓN
    // =========================================================

    public function test_puede_crear_un_paciente_con_datos_validos(): void
    {
        $catalogos = $this->crearCatalogos();
        $datos     = $this->datosPaciente($catalogos);

        $paciente = Paciente::create($datos);

        $this->assertInstanceOf(Paciente::class, $paciente);
        $this->assertDatabaseHas('paciente', [
            'numero_documento' => '1098765432',
            'correo'           => 'juan.perez@example.com',
        ]);
    }

    /** @test */
    public function test_puede_crear_un_paciente_sin_nombre2_y_apellido2(): void
    {
        $catalogos = $this->crearCatalogos();
        $datos     = $this->datosPaciente($catalogos);

        unset($datos['nombre2'], $datos['apellido2']);

        $paciente = Paciente::create($datos);

        $this->assertNull($paciente->nombre2);
        $this->assertNull($paciente->apellido2);
        $this->assertDatabaseHas('paciente', [
            'numero_documento' => '1098765432',
        ]);
    }

    // =========================================================
    // PRUEBAS DE RELACIONES
    // =========================================================

    public function test_tiene_relacion_con_tipo_documento(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $this->assertInstanceOf(
            TipoDocumento::class,
            $paciente->tipoDocumento
        );
        $this->assertEquals(
            'Cédula de Ciudadanía',
            $paciente->tipoDocumento->nombre
        );
    }

    public function test_tiene_relacion_con_genero(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $this->assertInstanceOf(Genero::class, $paciente->genero);
        $this->assertEquals('Masculino', $paciente->genero->nombre);
    }

    public function test_tiene_relacion_con_departamento(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $this->assertInstanceOf(Departamento::class, $paciente->departamento);
        $this->assertEquals('Cundinamarca', $paciente->departamento->nombre);
    }

    public function test_tiene_relacion_con_municipio(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $this->assertInstanceOf(Municipio::class, $paciente->municipio);
        $this->assertEquals('Bogotá', $paciente->municipio->nombre);
    }

    public function test_municipio_pertenece_al_departamento_correcto(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $this->assertEquals(
            $paciente->departamento_id,
            $paciente->municipio->departamento_id
        );
    }

    // =========================================================
    // PRUEBAS DE ACTUALIZACIÓN
    // =========================================================

    public function test_puede_actualizar_los_datos_de_un_paciente(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $paciente->update([
            'nombre1' => 'Pedro',
            'correo'  => 'pedro.nuevo@example.com',
        ]);

        $this->assertDatabaseHas('paciente', [
            'numero_documento' => '1098765432',
            'nombre1'          => 'Pedro',
            'correo'           => 'pedro.nuevo@example.com',
        ]);
    }

    // =========================================================
    // PRUEBAS DE ELIMINACIÓN
    // =========================================================

    public function test_puede_eliminar_un_paciente(): void
    {
        $catalogos = $this->crearCatalogos();
        $paciente  = Paciente::create($this->datosPaciente($catalogos));

        $paciente->delete();

        $this->assertDatabaseMissing('paciente', [
            'numero_documento' => '1098765432',
        ]);
    }

    // =========================================================
    // PRUEBAS DE BÚSQUEDA
    // =========================================================

    public function test_puede_buscar_paciente_por_numero_documento(): void
    {
        $catalogos = $this->crearCatalogos();
        Paciente::create($this->datosPaciente($catalogos));

        $encontrado = Paciente::where('numero_documento', '1098765432')->first();

        $this->assertNotNull($encontrado);
        $this->assertEquals('Juan', $encontrado->nombre1);
    }

    /** @test */
    public function test_puede_buscar_paciente_por_correo(): void
    {
        $catalogos = $this->crearCatalogos();
        Paciente::create($this->datosPaciente($catalogos));

        $encontrado = Paciente::where('correo', 'juan.perez@example.com')->first();

        $this->assertNotNull($encontrado);
        $this->assertEquals('1098765432', $encontrado->numero_documento);
    }

    public function test_retorna_null_si_paciente_no_existe(): void
    {
        $noExiste = Paciente::where('numero_documento', '9999999999')->first();

        $this->assertNull($noExiste);
    }
}