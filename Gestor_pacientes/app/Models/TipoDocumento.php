<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoDocumento extends Model
{
    use HasFactory;


    protected $table = 'tipos_documento';

    protected $fillable = [
        'nombre',
    ];

    
    public function paciente()
    {
        return $this->hasMany(Paciente::class, 'tipo_documento_id');
    }
}
