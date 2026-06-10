<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\XmlController;

Route::get('/', [XmlController::class, 'index']);
Route::get('/csrf-token', fn() => response()->json(['token' => csrf_token()]));
Route::post('/process', [XmlController::class, 'process']);
Route::post('/submit', [XmlController::class, 'submit']);
Route::post('/download', [XmlController::class, 'download']);
Route::post('/save-emission', [XmlController::class, 'saveEmission']);
Route::get('/historial', [XmlController::class, 'historial']);
Route::get('/api/emissions', [XmlController::class, 'emissionsApi']);
Route::get('/api/emissions/{id}', [XmlController::class, 'emissionDetail']);
