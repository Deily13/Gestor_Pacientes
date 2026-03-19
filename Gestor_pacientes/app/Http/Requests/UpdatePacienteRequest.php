<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Paciente;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $numeroDocumento = $this->route('numero_documento');
        $paciente        = Paciente::where('numero_documento', $numeroDocumento)->first();
        $pacienteId      = $paciente?->id;

        return [
            'tipo_documento_id' => 'sometimes|integer|exists:tipos_documento,id',
            'numero_documento'  => "sometimes|string|max:20|unique:paciente,numero_documento,{$pacienteId}",
            'nombre1'           => 'required|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/|max:50|min:3',
            'nombre2'           => 'nullable|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/|max:50',
            'apellido1'         => 'required|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/|max:50|min:3',
            'apellido2'         => 'nullable|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/|max:50',
            'genero_id'         => 'sometimes|integer|exists:genero,id',
            'departamento_id'   => 'sometimes|integer|exists:departamentos,id',
            'municipio_id'      => 'sometimes|integer|exists:municipios,id',
            'correo'            => "sometimes|email|unique:paciente,correo,{$pacienteId}",
            'foto_url'          => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento_id.exists' => 'El tipo de documento no es válido.',
            'numero_documento.unique'  => 'Este número de documento ya está registrado.',
            'genero_id.exists'         => 'El género seleccionado no es válido.',
            'departamento_id.exists'   => 'El departamento seleccionado no es válido.',
            'municipio_id.exists'      => 'El municipio seleccionado no es válido.',
            'correo.email'             => 'El formato del correo no es válido.',
            'correo.unique'            => 'Este correo ya está registrado.',
        ];
    }
}
