<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paciente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_documento_id');
            $table->string('numero_documento', 20)->unique(); 
            $table->string('nombre1', 50);
            $table->string('nombre2', 50)->nullable(); 
            $table->string('apellido1', 50);
            $table->string('apellido2', 50)->nullable();
            $table->unsignedBigInteger('genero_id');
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('municipio_id');
            $table->string('correo', 100)->nullable();

    
            $table->foreign('tipo_documento_id', 'fk_paciente_tipo_doc')->references('id')->on('tipos_documento');
            $table->foreign('genero_id', 'fk_paciente_genero')->references('id')->on('genero');
            $table->foreign('departamento_id', 'fk_paciente_depto')->references('id')->on('departamentos');
            $table->foreign('municipio_id', 'fk_paciente_muni')->references('id')->on('municipios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente');
    }
};
