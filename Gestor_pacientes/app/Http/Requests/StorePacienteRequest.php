<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'numero_documento'  => 'required|string|max:20|unique:paciente,numero_documento',
            'nombre1'           => 'required|string|min:2|max:50',
            'nombre2'           => 'nullable|string|max:50',
            'apellido1'         => 'required|string|min:2|max:50',
            'apellido2'         => 'nullable|string|max:50',
            'genero_id'         => 'required|integer|exists:genero,id',
            'departamento_id'   => 'required|integer|exists:departamentos,id',
            'municipio_id'      => 'required|integer|exists:municipios,id',
            'correo'            => 'required|email|unique:paciente,correo',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento_id.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento_id.exists'   => 'El tipo de documento no es válido.',
            'numero_documento.required'  => 'El número de documento es obligatorio.',
            'numero_documento.unique'    => 'Este número de documento ya está registrado.',
            'nombre1.required'           => 'El primer nombre es obligatorio.',
            'apellido1.required'         => 'El primer apellido es obligatorio.',
            'genero_id.required'         => 'El género es obligatorio.',
            'genero_id.exists'           => 'El género seleccionado no es válido.',
            'departamento_id.required'   => 'El departamento es obligatorio.',
            'departamento_id.exists'     => 'El departamento seleccionado no es válido.',
            'municipio_id.required'      => 'El municipio es obligatorio.',
            'municipio_id.exists'        => 'El municipio seleccionado no es válido.',
            'correo.required'            => 'El correo es obligatorio.',
            'correo.email'               => 'El formato del correo no es válido.',
            'correo.unique'              => 'Este correo ya está registrado.',
        ];
    }
}