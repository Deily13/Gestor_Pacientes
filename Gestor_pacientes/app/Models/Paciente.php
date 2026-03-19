<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Paciente extends Model
{
    use HasFactory;

    protected $table = 'paciente';

    protected $fillable = [
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
        'foto_url',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'genero_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }
}