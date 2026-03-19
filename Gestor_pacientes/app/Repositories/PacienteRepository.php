<?php

namespace App\Repositories;

use App\Models\Paciente;
use Illuminate\Pagination\LengthAwarePaginator;

class PacienteRepository
{
    private const RELATIONS = [
        'tipoDocumento',
        'genero',
        'departamento',
        'municipio',
    ];

    public function findByDocument(string $numeroDocumento): ?Paciente
    {
        return Paciente::where('numero_documento', $numeroDocumento)->first();
    }

    public function findByEmail(string $correo): ?Paciente
    {
        return Paciente::where('correo', $correo)->first();
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Paciente::with(self::RELATIONS)->paginate($perPage);
    }
}