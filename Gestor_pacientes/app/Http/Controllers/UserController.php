<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Repositories\PacienteRepository;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(
        private PacienteRepository $repository
    ) {}

    /**
     * Listar todos los pacientes.
     */
    public function index(): JsonResponse
    {
        $pacientes = Paciente::with([
            'tipoDocumento',
            'genero',
            'departamento',
            'municipio'
        ])->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $pacientes,
        ]);
    }

    /**
     * Crear un nuevo paciente.
     */
    public function store(StorePacienteRequest $request): JsonResponse
    {
        $existe = $this->repository->findByDocument($request->numero_documento);

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'El número de documento ya está registrado.',
            ], 422);
        }

        $paciente = Paciente::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Paciente creado correctamente.',
            'data'    => $paciente->load([
                'tipoDocumento',
                'genero',
                'departamento',
                'municipio'
            ]),
        ], 201);
    }

    /**
     * Mostrar un paciente por numero_documento.
     */
    public function show(string $numero_documento): JsonResponse
    {
        $paciente = Paciente::with([
            'tipoDocumento',
            'genero',
            'departamento',
            'municipio'
        ])->where('numero_documento', $numero_documento)->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $paciente,
        ]);
    }

    /**
     * Actualizar un paciente por numero_documento.
     */
    public function update(UpdatePacienteRequest $request, string $numero_documento): JsonResponse
    {
        $paciente = Paciente::where('numero_documento', $numero_documento)->firstOrFail();
        $paciente->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Paciente actualizado correctamente.',
            'data'    => $paciente->fresh()->load([
                'tipoDocumento',
                'genero',
                'departamento',
                'municipio'
            ]),
        ]);
    }

    /**
     * Eliminar un paciente por numero_documento.
     */
    public function destroy(string $numero_documento): JsonResponse
    {
        $paciente = Paciente::where('numero_documento', $numero_documento)->firstOrFail();
        $paciente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paciente eliminado correctamente.',
        ]);
    }

    public function uploadFoto(Request $request): JsonResponse
{
    $request->validate([
        'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $path = $request->file('foto')->store('pacientes/fotos', 'public');

    return response()->json([
        'success'  => true,
        'foto_url' => Storage::url($path),
    ]);
}
}