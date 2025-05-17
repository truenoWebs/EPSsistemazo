<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Importante para validación
use Illuminate\Support\Facades\DB; // Importante para consultas compuestas
use Illuminate\Validation\Rule; // Necesario para validación Unique en Update

class EspecialidadController extends Controller
{
    /**
     * Muestra una lista de las especialidades.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $especialidades = Especialidad::all();
        return response()->json([
            'status' => true,
            'data' => $especialidades,
            'message' => 'Lista de especialidades obtenida correctamente'
        ], 200);
    }

    /**
     * Almacena una nueva especialidad en la base de datos.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_especialidad' => 'required|string|max:100|unique:especialidades,nombre_especialidad',
            'descripcion_especialidad' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $especialidad = Especialidad::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $especialidad,
            'message' => 'Especialidad creada correctamente'
        ], 201);
    }

    /**
     * Muestra la especialidad especificada.
     * @param  \App\Models\Especialidad  $especialidad
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Especialidad $especialidad) // Route Model Binding
    {
        // Cargar relaciones si se quisiera, ej: $especialidad->load('medicos');
        return response()->json([
            'status' => true,
            'data' => $especialidad,
            'message' => 'Especialidad encontrada'
        ], 200);
    }

    /**
     * Actualiza la especialidad especificada en la base de datos.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Especialidad  $especialidad
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Especialidad $especialidad) // Route Model Binding
    {
        $validator = Validator::make($request->all(), [
            // Validar que el nombre sea único, ignorando el registro actual
            'nombre_especialidad' => ['sometimes','required','string','max:100', Rule::unique('especialidades', 'nombre_especialidad')->ignore($especialidad->id)],
            'descripcion_especialidad' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $especialidad->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $especialidad,
            'message' => 'Especialidad actualizada correctamente'
        ], 200);
    }

    /**
     * Elimina la especialidad especificada de la base de datos.
     * @param  \App\Models\Especialidad  $especialidad
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Especialidad $especialidad) // Route Model Binding
    {
        // Considerar lógica de negocio: ¿se puede eliminar si tiene médicos asociados?
        // Podrías verificar $especialidad->medicos()->count() == 0 antes de eliminar.
        try {
            $especialidad->delete();
            return response()->json([
                'status' => true,
                'message' => 'Especialidad eliminada correctamente'
            ], 200); // O 204 No Content si no devuelves cuerpo
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error si hay restricción de llave foránea (ej. si médicos usan onDelete('restrict'))
            return response()->json([
                'status' => false,
                'message' => 'No se pudo eliminar la especialidad, probablemente tiene médicos asociados.',
                'error_code' => $e->getCode() // Opcional: código de error de BD
            ], 409); // 409 Conflict es apropiado
        }

    }

    /**
     * Muestra el número de citas agrupadas por especialidad.
     * (Consulta Compuesta #4 - Corregida/Verificada)
     * @return \Illuminate\Http\JsonResponse
     */
    public function conteoCitasPorEspecialidad()
    {
        // Usando Query Builder para mayor claridad y compatibilidad
        $conteo = DB::table('especialidades')
            ->select('especialidades.nombre_especialidad', DB::raw('COUNT(citas.id) as total_citas'))
            // Usamos leftJoin para incluir especialidades sin citas (con conteo 0)
            ->leftJoin('citas', 'especialidades.id', '=', 'citas.id_especialidad_cita')
            // Agrupamos por el id (PK) y el nombre para evitar problemas con ONLY_FULL_GROUP_BY
            ->groupBy('especialidades.id', 'especialidades.nombre_especialidad')
            ->orderBy('total_citas', 'desc') // Ordenar por número de citas
            ->orderBy('especialidades.nombre_especialidad', 'asc') // Orden secundario alfabético
            ->get();

        return response()->json([
            'status' => true,
            'data' => $conteo,
            'message' => 'Conteo de citas por especialidad obtenido correctamente.'
        ]);
    }
}
