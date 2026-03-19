<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/pacientes'));
Route::get('/login',     fn() => view('auth.login'));
Route::get('/pacientes', fn() => view('pacientes.index'));
