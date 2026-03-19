<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Paciente;
use App\Models\TipoDocumento;
use App\Models\Genero;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class UpdatePacienteRequestTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // CONFIGURACIÓN INICIAL
    // =========================================================

    private array $catalogos;
    private Paciente $pacienteExistente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogos         = $this->inicializarCatalogos();
        $this->pacienteExistente = $this->registrarPaciente();
    }

    private function inicializarCatalogos(): array
    {
        $tipoDoc  = TipoDocumento::create(['nombre' => 'Cédula de Ciudadanía']);
        $sexo     = Genero::create(['nombre' => 'Femenino']);
        $region   = Departamento::create(['nombre' => 'Valle del Cauca']);
        $ciudad   = Municipio::create([
            'nombre'          => 'Cali',
            'departamento_id' => $region->id,
        ]);

        return [
            'tipo_documento_id' => $tipoDoc->id,
            'genero_id'         => $sexo->id,
            'departamento_id'   => $region->id,
            'municipio_id'      => $ciudad->id,
        ];
    }

    private function registrarPaciente(array $cambios = []): Paciente
    {
        return Paciente::create(array_merge([
            'tipo_documento_id' => $this->catalogos['tipo_documento_id'],
            'numero_documento'  => '1098765432',
            'nombre1'           => 'Laura',
            'nombre2'           => 'Sofía',
            'apellido1'         => 'Ospina',
            'apellido2'         => 'Torres',
            'genero_id'         => $this->catalogos['genero_id'],
            'departamento_id'   => $this->catalogos['departamento_id'],
            'municipio_id'      => $this->catalogos['municipio_id'],
            'correo'            => 'laura.ospina@gmail.com',
        ], $cambios));
    }

    private function validarActualizacion(array $datos, string $numeroDocumento): \Illuminate\Validation\Validator
    {
        $request = new UpdatePacienteRequest();
        $request->setRouteResolver(function () use ($numeroDocumento) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/api/patients/{numero_documento}', []);
            $route->bind(
                \Illuminate\Http\Request::create("/api/patients/{$numeroDocumento}", 'PUT')
            );
            $route->setParameter('numero_documento', $numeroDocumento);
            return $route;
        });

        return Validator::make($datos, $request->rules(), $request->messages());
    }

    // =========================================================
    // VALIDACIÓN EXITOSA
    // =========================================================

    public function test_actualizacion_con_datos_validos_pasa_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre1' => 'Valentina'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_todos_los_campos_son_opcionales_en_actualizacion(): void
    {
        $validador = $this->validarActualizacion([], '1098765432');

        $this->assertFalse($validador->fails());
    }

    public function test_puede_actualizar_solo_el_correo(): void
    {
        $validador = $this->validarActualizacion(
            ['correo' => 'valentina.nueva@gmail.com'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_puede_actualizar_solo_el_nombre(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre1' => 'Valentina', 'nombre2' => 'Isabel'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_mismo_correo_del_paciente_no_falla_unique(): void
    {
        $validador = $this->validarActualizacion(
            ['correo' => 'laura.ospina@gmail.com'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_mismo_numero_documento_del_paciente_no_falla_unique(): void
    {
        $validador = $this->validarActualizacion(
            ['numero_documento' => '1098765432'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    // =========================================================
    // VALIDACIÓN tipo_documento_id
    // =========================================================

    public function test_tipo_documento_invalido_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['tipo_documento_id' => 9999],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El tipo de documento no es válido.',
            $validador->errors()->first('tipo_documento_id')
        );
    }

    public function test_tipo_documento_debe_ser_entero(): void
    {
        $validador = $this->validarActualizacion(
            ['tipo_documento_id' => 'pasaporte'],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('tipo_documento_id', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN numero_documento
    // =========================================================

    public function test_numero_documento_duplicado_falla_validacion(): void
    {
        $this->registrarPaciente([
            'numero_documento' => '9876543210',
            'correo'           => 'segundo.paciente@gmail.com',
        ]);

        $validador = $this->validarActualizacion(
            ['numero_documento' => '9876543210'],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'Este número de documento ya está registrado.',
            $validador->errors()->first('numero_documento')
        );
    }

    public function test_numero_documento_no_puede_superar_20_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['numero_documento' => '123456789012345678901'],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('numero_documento', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN nombre1 y apellido1
    // =========================================================

    public function test_nombre1_debe_tener_minimo_2_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre1' => 'Jo'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_nombre1_con_un_solo_caracter_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre1' => 'Ana'[0]],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('nombre1', $validador->errors()->toArray());
    }

    public function test_nombre1_no_puede_superar_50_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre1' => 'Alejandro' . str_repeat('andres', 8)],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('nombre1', $validador->errors()->toArray());
    }

    public function test_apellido1_debe_tener_minimo_2_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['apellido1' => 'Ro'],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_apellido1_con_un_solo_caracter_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['apellido1' => 'García'[0]],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('apellido1', $validador->errors()->toArray());
    }

    public function test_apellido1_no_puede_superar_50_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['apellido1' => 'Rodriguez' . str_repeat('mendoza', 7)],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('apellido1', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN nombre2 y apellido2 (opcionales)
    // =========================================================

    public function test_nombre2_puede_ser_nulo(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre2' => null],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_apellido2_puede_ser_nulo(): void
    {
        $validador = $this->validarActualizacion(
            ['apellido2' => null],
            '1098765432'
        );

        $this->assertFalse($validador->fails());
    }

    public function test_nombre2_no_puede_superar_50_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['nombre2' => 'Carolina' . str_repeat('camila', 8)],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('nombre2', $validador->errors()->toArray());
    }

    public function test_apellido2_no_puede_superar_50_caracteres(): void
    {
        $validador = $this->validarActualizacion(
            ['apellido2' => 'Ramirez' . str_repeat('moreno', 8)],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertArrayHasKey('apellido2', $validador->errors()->toArray());
    }

    // =========================================================
    // VALIDACIÓN genero_id
    // =========================================================

    public function test_genero_invalido_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['genero_id' => 9999],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El género seleccionado no es válido.',
            $validador->errors()->first('genero_id')
        );
    }

    // =========================================================
    // VALIDACIÓN departamento_id y municipio_id
    // =========================================================

    public function test_departamento_invalido_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['departamento_id' => 9999],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El departamento seleccionado no es válido.',
            $validador->errors()->first('departamento_id')
        );
    }

    public function test_municipio_invalido_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['municipio_id' => 9999],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El municipio seleccionado no es válido.',
            $validador->errors()->first('municipio_id')
        );
    }

    // =========================================================
    // VALIDACIÓN correo
    // =========================================================

    public function test_formato_de_correo_invalido_falla_validacion(): void
    {
        $validador = $this->validarActualizacion(
            ['correo' => 'correo-sin-arroba-ni-dominio'],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'El formato del correo no es válido.',
            $validador->errors()->first('correo')
        );
    }

    public function test_correo_duplicado_de_otro_paciente_falla_validacion(): void
    {
        $this->registrarPaciente([
            'numero_documento' => '9876543210',
            'correo'           => 'maria.gonzalez@gmail.com',
        ]);

        $validador = $this->validarActualizacion(
            ['correo' => 'maria.gonzalez@gmail.com'],
            '1098765432'
        );

        $this->assertTrue($validador->fails());
        $this->assertEquals(
            'Este correo ya está registrado.',
            $validador->errors()->first('correo')
        );
    }
}