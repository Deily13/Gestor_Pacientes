<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorePacienteRequest;

class StorePacienteRequestTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // CONFIGURACIÓN INICIAL
    // =========================================================

    private array $catalogos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogos = $this->inicializarCatalogos();
    }

    private function inicializarCatalogos(): array
    {
        $tipoDoc      = TipoDocumento::create(['nombre' => 'Cédula de Ciudadanía']);
        $sexo         = Genero::create(['nombre' => 'Masculino']);
        $region       = Departamento::create(['nombre' => 'Antioquia']);
        $ciudad       = Municipio::create([
            'nombre'          => 'Medellín',
            'departamento_id' => $region->id,
        ]);

        return [
            'tipo_documento_id' => $tipoDoc->id,
            'genero_id'         => $sexo->id,
            'departamento_id'   => $region->id,
            'municipio_id'      => $ciudad->id,
        ];
    }

    private function formularioCompleto(array $cambios = []): array
    {
        return array_merge([
            'tipo_documento_id' => $this->catalogos['tipo_documento_id'],
            'numero_documento'  => '1098765432',
            'nombre1'           => 'Carlos',
            'nombre2'           => 'Andrés',
            'apellido1'         => 'Rodríguez',
            'apellido2'         => 'Mejía',
            'genero_id'         => $this->catalogos['genero_id'],
            'departamento_id'   => $this->catalogos['departamento_id'],
            'municipio_id'      => $this->catalogos['municipio_id'],
            'correo'            => 'carlos.rodriguez@gmail.com',
        ], $cambios);
    }

    private function validar(array $datos): \Illuminate\Validation\Validator
    {
        $request = new StorePacienteRequest();
        return Validator::make($datos, $request->rules(), $request->messages());
    }

    // =========================================================
    // VALIDACIÓN EXITOSA
    // =========================================================

    public function test_formulario_completo_pasa_validacion(): void
    {
        $validador = $this->validar($this->formularioCompleto());

        $this->assertFalse($validador->fails());
    }

    public function test_campos_opcionales_pueden_estar_vacios(): void
    {
        $datos     = $this->formularioCompleto(['nombre2' => null, 'apellido2' => null]);
        $validador = $this->validar($datos);

        $this->assertFalse($validador->fails());
    }

    // =========================================================
    // VALIDACIÓN tipo_documento_id
    // =========================================================

    public function test_tipo_documento_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['tipo_documento_id' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('tipo_documento_id', $validador->errors()->toArray());
    }

    public function test_tipo_documento_debe_existir_en_base_de_datos(): void
    {
        $datos     = $this->formularioCompleto(['tipo_documento_id' => 9999]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El tipo de documento no es válido.',
            $validador->errors()->first('tipo_documento_id')
        );
    }

    public function test_tipo_documento_debe_ser_entero(): void
    {
        $datos     = $this->formularioCompleto(['tipo_documento_id' => 'abc']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('tipo_documento_id', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN numero_documento
    // =========================================================

    public function test_numero_documento_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['numero_documento' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El número de documento es obligatorio.',
            $validador->errors()->first('numero_documento')
        );
    }

    public function test_numero_documento_no_puede_superar_20_caracteres(): void
    {
        $datos     = $this->formularioCompleto(['numero_documento' => str_repeat('1', 21)]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('numero_documento', $validador->errors()->toArray());
    }

    public function test_numero_documento_duplicado_falla_validacion(): void
    {
        Paciente::create($this->formularioCompleto());

        $datos     = $this->formularioCompleto(['correo' => 'otro@gmail.com']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'Este número de documento ya está registrado.',
            $validador->errors()->first('numero_documento')
        );
    }

    // =========================================================
    // VALIDACIÓN nombre1 y apellido1
    // =========================================================

    public function test_primer_nombre_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['nombre1' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El primer nombre es obligatorio.',
            $validador->errors()->first('nombre1')
        );
    }

    public function test_primer_nombre_debe_tener_minimo_2_caracteres(): void
    {
        $datos     = $this->formularioCompleto(['nombre1' => 'A']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('nombre1', $validador->errors()->toArray());
    }

    public function test_primer_nombre_no_puede_superar_50_caracteres(): void
    {
        $datos     = $this->formularioCompleto(['nombre1' => str_repeat('A', 51)]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('nombre1', $validador->errors()->toArray());
    }

    public function test_primer_apellido_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['apellido1' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El primer apellido es obligatorio.',
            $validador->errors()->first('apellido1')
        );
    }

    public function test_primer_apellido_debe_tener_minimo_2_caracteres(): void
    {
        $datos     = $this->formularioCompleto(['apellido1' => 'R']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('apellido1', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN genero_id
    // =========================================================

    public function test_genero_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['genero_id' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El género es obligatorio.',
            $validador->errors()->first('genero_id')
        );
    }

    public function test_genero_debe_existir_en_base_de_datos(): void
    {
        $datos     = $this->formularioCompleto(['genero_id' => 9999]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El género seleccionado no es válido.',
            $validador->errors()->first('genero_id')
        );
    }

    // =========================================================
    // VALIDACIÓN departamento_id y municipio_id
    // =========================================================

    public function test_departamento_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['departamento_id' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El departamento es obligatorio.',
            $validador->errors()->first('departamento_id')
        );
    }

    public function test_departamento_debe_existir_en_base_de_datos(): void
    {
        $datos     = $this->formularioCompleto(['departamento_id' => 9999]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El departamento seleccionado no es válido.',
            $validador->errors()->first('departamento_id')
        );
    }

    public function test_municipio_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['municipio_id' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El municipio es obligatorio.',
            $validador->errors()->first('municipio_id')
        );
    }

    public function test_municipio_debe_existir_en_base_de_datos(): void
    {
        $datos     = $this->formularioCompleto(['municipio_id' => 9999]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El municipio seleccionado no es válido.',
            $validador->errors()->first('municipio_id')
        );
    }

    // =========================================================
    // VALIDACIÓN correo
    // =========================================================

    public function test_correo_es_obligatorio(): void
    {
        $datos     = $this->formularioCompleto(['correo' => null]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El correo es obligatorio.',
            $validador->errors()->first('correo')
        );
    }

    public function test_correo_debe_tener_formato_valido(): void
    {
        $datos     = $this->formularioCompleto(['correo' => 'esto-no-es-un-correo']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El formato del correo no es válido.',
            $validador->errors()->first('correo')
        );
    }

    public function test_correo_duplicado_falla_validacion(): void
    {
        Paciente::create($this->formularioCompleto());

        $datos     = $this->formularioCompleto(['numero_documento' => '9876543210']);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'Este correo ya está registrado.',
            $validador->errors()->first('correo')
        );
    }

    public function test_multiples_errores_se_reportan_simultaneamente(): void
    {
        $validador = $this->validar([]);

        $this->assertTrue($validador->fails());
        $errores = $validador->errors()->toArray();

        $this->assertArrayHasKey('tipo_documento_id', $errores);
        $this->assertArrayHasKey('numero_documento',  $errores);
        $this->assertArrayHasKey('nombre1',           $errores);
        $this->assertArrayHasKey('apellido1',         $errores);
        $this->assertArrayHasKey('genero_id',         $errores);
        $this->assertArrayHasKey('departamento_id',   $errores);
        $this->assertArrayHasKey('municipio_id',      $errores);
        $this->assertArrayHasKey('correo',            $errores);
    }
}