<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
        protected $table    = 'departamentos';
    protected $fillable = ['nombre'];  // ← faltaba esto

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'departamento_id');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'departamento_id');
    }
}
