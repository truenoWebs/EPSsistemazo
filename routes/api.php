<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Importa tus controladores de API
use App\Http\Controllers\API\UsuarioController;
use App\Http\Controllers\API\EspecialidadController;
use App\Http\Controllers\API\MedicoController;
use App\Http\Controllers\API\PacienteController;
use App\Http\Controllers\API\CitaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ruta de prueba básica para la API (opcional)
Route::get('/saludo', function () {
    return response()->json(['mensaje' => 'Bienvenido a la API de la EPS']);
});
Route::get('pacientes/top-citas', [PacienteController::class, 'pacientesConMasCitas']);

Route::get('especialidades/conteo-citas', [EspecialidadController::class, 'conteoCitasPorEspecialidad']);
// Rutas para Usuarios (si decides exponerlas, considera la seguridad)
Route::apiResource('usuarios', UsuarioController::class);

// Rutas para Especialidades
Route::apiResource('especialidades', EspecialidadController::class);

// Rutas para Medicos
Route::apiResource('medicos', MedicoController::class);

// Rutas para Pacientes
Route::apiResource('pacientes', PacienteController::class);

// Rutas para Citas
Route::apiResource('citas', CitaController::class);



Route::get('medicos/por-especialidad/{id_especialidad}', [App\Http\Controllers\API\MedicoController::class, 'porEspecialidad']);

Route::get('citas/medico/{id_medico}/rango-fechas', [App\Http\Controllers\API\CitaController::class, 'citasPorMedicoYFechas']);

Route::get('pacientes/top-citas', [App\Http\Controllers\API\PacienteController::class, 'pacientesConMasCitas']);
// Ejemplo de uso en Postman: GET /api/pacientes/top-citas?limite=3&estado_cita=Realizada



Route::get('citas/paciente/{id_paciente}/proximas', [App\Http\Controllers\API\CitaController::class, 'proximasCitasPorPaciente']);

// Ejemplo de ruta para una consulta específica si es necesario
// Route::get('medicos/especialidad/{idEspecialidad}', [MedicoController::class, 'medicosPorEspecialidad']);

// Rutas de Autenticación (ejemplo básico, necesitarás implementar la lógica)
// Route::post('/registro', [AuthController::class, 'registrar']);
// Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por autenticación (ejemplo)
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/usuario-actual', function (Request $request) {
//         return $request->user();
//     });
//     // Aquí podrías mover algunas de las rutas apiResource que requieran autenticación.
// });
