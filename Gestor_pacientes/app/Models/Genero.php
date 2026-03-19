<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genero extends Model
{
     use HasFactory;

   protected $table    = 'genero';
    protected $fillable = ['nombre'];

    public function paciente()
    {
        return $this->hasMany(Paciente::class, 'genero_id');
    }
}
