<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/pacientes'));
Route::get('/pacientes', fn() => view('pacientes.index'));
