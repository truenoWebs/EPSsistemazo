<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Muestra una lista de las citas.
     */
    public function index(Request $request)
    {
        // Ejemplo de filtrado básico: por estado o por paciente_id
        $query = Cita::with(['paciente', 'medico', 'especialidad']);

        if ($request->has('estado_cita')) {
            $query->where('estado_cita', $request->estado_cita);
        }

        if ($request->has('id_paciente')) {
            $query->where('id_paciente', $request->id_paciente);
        }
        if ($request->has('id_medico')) {
            $query->where('id_medico', $request->id_medico);
        }

        $citas = $query->orderBy('fecha_hora_cita', 'desc')->paginate(15); // Paginado

        return response()->json([
            'status' => true,
            'data' => $citas,
            'message' => 'Lista de citas obtenida correctamente'
        ]);
    }

    /**
     * Almacena una nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        $reglas = [
            'id_paciente' => 'required|integer|exists:pacientes,id',
            'id_medico' => 'required|integer|exists:medicos,id',
            'id_especialidad_cita' => 'required|integer|exists:especialidades,id',
            'fecha_hora_cita' => 'sometimes|required|date_format:Y-m-d H:i:s' . ($request->has('fecha_hora_cita') ? '|after_or_equal:now' : ''),
            'estado_cita' => ['nullable','string','max:50',Rule::in(['Programada', 'Cancelada', 'Realizada', 'No Asistió'])],
            'motivo_cita' => 'nullable|string',
            'observaciones_cita' => 'nullable|string',
        ];
        $mensajes = [
            'id_paciente.required' => 'El paciente es obligatorio.',
            'id_paciente.exists' => 'El paciente seleccionado no existe.',
            'id_medico.required' => 'El médico es obligatorio.',
            'id_medico.exists' => 'El médico seleccionado no existe.',
            'id_especialidad_cita.required' => 'La especialidad es obligatoria.',
            'id_especialidad_cita.exists' => 'La especialidad seleccionada no existe.',
            'fecha_hora_cita.required' => 'La fecha y hora de la cita son obligatorias.',
            'fecha_hora_cita.date_format' => 'El formato de fecha y hora debe ser AAAA-MM-DD HH:MM:SS.',
            'fecha_hora_cita.after_or_equal' => 'La fecha y hora de la cita no puede ser en el pasado.',
            'estado_cita.in' => 'El estado de la cita no es válido.',
        ];

        $validador = Validator::make($request->all(), $reglas);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        // Lógica adicional: Verificar disponibilidad del médico, no solapamiento de citas, etc. (más complejo)
        // Por ahora, creación directa:
        $datosCita = $request->all();
        if(!$request->filled('estado_cita')){
            $datosCita['estado_cita'] = 'Programada'; // Estado por defecto si no se envía
        }

        $cita = Cita::create($datosCita);

        return response()->json([
            'status' => true,
            'data' => $cita,
            'message' => 'Cita creada correctamente'
        ], 201);
    }

    /**
     * Muestra la cita especificada.
     */
    public function show(Cita $cita)
    {
        $cita->load(['paciente', 'medico', 'especialidad']);
        return response()->json([
            'status' => true,
            'data' => $cita,
            'message' => 'Cita encontrada'
        ]);
    }

    /**
     * Actualiza la cita especificada en la base de datos.
     */
    public function update(Request $request, Cita $cita)
    {
        $reglas = [
            'id_paciente' => 'sometimes|required|integer|exists:pacientes,id',
            'id_medico' => 'sometimes|required|integer|exists:medicos,id',
            'id_especialidad_cita' => 'sometimes|required|integer|exists:especialidades,id',
            'fecha_hora_cita' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'estado_cita' => ['sometimes','required','string','max:50',Rule::in(['Programada', 'Cancelada', 'Realizada', 'No Asistió'])],
            'motivo_cita' => 'nullable|string',
            'observaciones_cita' => 'nullable|string',
        ];
        // Añadir mensajes personalizados si se desea

        $validador = Validator::make($request->all(), $reglas);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        // Lógica adicional: al reprogramar, verificar disponibilidad, etc.
        $cita->update($request->all());

        return response()->json([
            'status' => true,
            'data' => $cita,
            'message' => 'Cita actualizada correctamente'
        ]);
    }

    /**
     * Elimina la cita especificada de la base de datos.
     */
    public function destroy(Cita $cita)
    {
        // Considerar si se pueden eliminar citas pasadas o solo cancelar futuras.
        $cita->delete();
        return response()->json([
            'status' => true,
            'message' => 'Cita eliminada correctamente'
        ]);
    }

    public function citasPorMedicoYFechas(Request $request, $id_medico)
    {
        $reglas = [
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
        ];
        $mensajes = [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date_format' => 'Formato de fecha de inicio inválido (AAAA-MM-DD).',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date_format' => 'Formato de fecha de fin inválido (AAAA-MM-DD).',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];

        // Validar también el id_medico
        $validadorMedico = Validator::make(['id_medico' => $id_medico], [
            'id_medico' => 'required|integer|exists:medicos,id'
        ]);

        if ($validadorMedico->fails()) {
            return response()->json(['status' => false, 'errors' => $validadorMedico->errors()], 400);
        }

        $validadorFechas = Validator::make($request->all(), $reglas, $mensajes);

        if ($validadorFechas->fails()) {
            return response()->json(['status' => false, 'errors' => $validadorFechas->errors()], 400);
        }

        $fechaInicio = $request->input('fecha_inicio') . " 00:00:00";
        $fechaFin = $request->input('fecha_fin') . " 23:59:59";

        $citas = Cita::where('id_medico', $id_medico)
            ->whereBetween('fecha_hora_cita', [$fechaInicio, $fechaFin])
            ->with(['paciente', 'especialidad']) // Cargar relaciones
            ->orderBy('fecha_hora_cita', 'asc')
            ->get();

        if ($citas->isEmpty()) {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'No se encontraron citas para el médico en el rango de fechas especificado.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $citas,
            'message' => 'Citas del médico obtenidas correctamente.'
        ]);
    }

    /**
     * Muestra las próximas citas programadas para un paciente específico.
     */
    public function proximasCitasPorPaciente($id_paciente)
    {
        $validador = Validator::make(['id_paciente' => $id_paciente], [
            'id_paciente' => 'required|integer|exists:pacientes,id'
        ]);

        if ($validador->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validador->errors()
            ], 400);
        }

        $citas = Cita::where('id_paciente', $id_paciente)
            ->where('estado_cita', 'Programada')
            ->where('fecha_hora_cita', '>=', now()) // Desde ahora en adelante
            ->with(['medico.especialidad', 'especialidad']) // Cargar médico con su especialidad, y la especialidad de la cita
            ->orderBy('fecha_hora_cita', 'asc')
            ->get();

        if ($citas->isEmpty()) {
            return response()->json([
                'status' => true,
                'data' => [],
                'message' => 'El paciente no tiene próximas citas programadas.'
            ], 200); // O 404 si prefieres
        }

        return response()->json([
            'status' => true,
            'data' => $citas,
            'message' => 'Próximas citas del paciente obtenidas correctamente.'
        ]);
    }
}
