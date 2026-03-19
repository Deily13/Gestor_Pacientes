<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table    = 'municipios';
    protected $fillable = ['nombre', 'departamento_id'];  // ← verifica que esté

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'municipio_id');
    }
}
