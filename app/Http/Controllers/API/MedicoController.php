<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MedicoController extends Controller
{
    /**
     * Muestra una lista de los médicos.
     */
    public function index()
    {
        $medicos = Medico::with('especialidad')->get(); // Cargar la relación con especialidad
        return response()->json([
            'status' => true,
            'data' => $medicos,
            'message' => 'Lista de médicos obtenida correctamente'
        ]);
    }

    /**
     * Almacena un nuevo médico en la base de datos.
     */
    public function store(Request $request)
    {
        $reglas = [
            'numero_documento_medico' => 'required|string|max:20|unique:medicos,numero_documento_medico',
            'tipo_documento_medico' => 'required|string|max:50',
            'nombres_medico' => 'required|string|max:100',
            'apellidos_medico' => 'required|string|max:100',
            'tarjeta_profesional' => 'required|string|max:50|unique:medicos,tarjeta_profesional',
            'telefono_medico' => 'nullable|string|max:20',
            'email_medico' => 'nullable|string|email|max:100|unique:medicos,email_medico',
            'id_especialidad' => 'required|integer|exists:especialidades,id',
        ];
        $mensajes = [
            'numero_documento_medico.required' => 'El número de documento es obligatorio.',
            'numero_documento_medico.unique' => 'Este número de documento ya está registrado.',
            'id_especialidad.required' => 'La especialidad es obligatoria.',
            'id_especialidad.exists' => 'La especialidad seleccionada no es válida.',
            // Añadir más mensajes personalizados según necesidad
        ];

        $validador = Validator::make($request->all(), $reglas, $mensajes);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        $medico = Medico::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $medico,
            'message' => 'Médico creado correctamente'
        ], 201);
    }

    /**
     * Muestra el médico especificado.
     */
    public function show(Medico $medico)
    {
        $medico->load('especialidad', 'citas'); // Cargar relaciones
        return response()->json([
            'status' => true,
            'data' => $medico,
            'message' => 'Médico encontrado'
        ]);
    }

    /**
     * Actualiza el médico especificado en la base de datos.
     */
    public function update(Request $request, Medico $medico)
    {
        $reglas = [
            'numero_documento_medico' => ['sometimes','required','string','max:20',Rule::unique('medicos', 'numero_documento_medico')->ignore($medico->id)],
            'tipo_documento_medico' => 'sometimes|required|string|max:50',
            'nombres_medico' => 'sometimes|required|string|max:100',
            'apellidos_medico' => 'sometimes|required|string|max:100',
            'tarjeta_profesional' => ['sometimes','required','string','max:50',Rule::unique('medicos', 'tarjeta_profesional')->ignore($medico->id)],
            'telefono_medico' => 'nullable|string|max:20',
            'email_medico' => ['nullable','string','email','max:100',Rule::unique('medicos', 'email_medico')->ignore($medico->id)],
            'id_especialidad' => 'sometimes|required|integer|exists:especialidades,id',
        ];
        // Añadir mensajes personalizados si se desea

        $validador = Validator::make($request->all(), $reglas);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        $medico->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $medico,
            'message' => 'Médico actualizado correctamente'
        ]);
    }

    /**
     * Elimina el médico especificado de la base de datos.
     */
    public function destroy(Medico $medico)
    {
        // Considerar si se pueden eliminar médicos con citas activas o futuras
        $medico->delete();
        return response()->json([
            'status' => true,
            'message' => 'Médico eliminado correctamente'
        ]);
    }

    public function porEspecialidad($id_especialidad)
    {
        $validador = Validator::make(['id_especialidad' => $id_especialidad], [
            'id_especialidad' => 'required|integer|exists:especialidades,id'
        ]);

        if ($validador->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validador->errors()
            ], 400);
        }

        $medicos = Medico::where('id_especialidad', $id_especialidad)
            ->with('especialidad') // Opcional, para incluir datos de la especialidad
            ->get();

        if ($medicos->isEmpty()) {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'No se encontraron médicos para la especialidad indicada.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $medicos,
            'message' => 'Médicos por especialidad obtenidos correctamente.'
        ]);
    }
}
