<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Paciente; // Asegúrate que el namespace del modelo sea correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Para loguear errores si es necesario

class PacienteController extends Controller
{
    /**
     * Muestra una lista de los pacientes.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Considerar paginación para listas largas: Paciente::paginate(15);
        $pacientes = Paciente::orderBy('apellidos_paciente', 'asc')->orderBy('nombres_paciente', 'asc')->get(); // Ordenar alfabéticamente
        return response()->json([
            'status' => true,
            'data' => $pacientes,
            'message' => 'Lista de pacientes obtenida correctamente'
        ]);
    }

    /**
     * Almacena un nuevo paciente en la base de datos.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $reglas = [
            'numero_documento_paciente' => 'required|string|max:20|unique:pacientes,numero_documento_paciente',
            'tipo_documento_paciente' => 'required|string|max:50',
            'nombres_paciente' => 'required|string|max:100',
            'apellidos_paciente' => 'required|string|max:100',
            'fecha_nacimiento_paciente' => 'required|date_format:Y-m-d',
            'genero_paciente' => 'nullable|string|max:20',
            'direccion_paciente' => 'nullable|string|max:255',
            'telefono_paciente' => 'nullable|string|max:20',
            'email_paciente' => 'nullable|string|email|max:100|unique:pacientes,email_paciente',
            'eps_paciente' => 'nullable|string|max:100',
        ];
        $mensajes = [
            'numero_documento_paciente.required' => 'El número de documento es obligatorio.',
            'numero_documento_paciente.unique' => 'Este número de documento ya está registrado.',
            'fecha_nacimiento_paciente.date_format' => 'La fecha de nacimiento debe tener el formato AAAA-MM-DD.',
            'email_paciente.unique' => 'Este correo electrónico ya está registrado para otro paciente.',
            'email_paciente.email' => 'El formato del correo electrónico no es válido.',
        ];

        $validador = Validator::make($request->all(), $reglas, $mensajes);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        $paciente = Paciente::create($request->all());

        return response()->json([
            'status' => true,
            'data' => $paciente,
            'message' => 'Paciente creado correctamente'
        ], 201);
    }

    /**
     * Muestra el paciente especificado.
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Paciente $paciente) // Route Model Binding
    {
        // Cargar relaciones opcionalmente si se necesitan siempre
        // $paciente->load('citas', 'usuario');
        return response()->json([
            'status' => true,
            'data' => $paciente,
            'message' => 'Paciente encontrado'
        ]);
    }

    /**
     * Actualiza el paciente especificado en la base de datos.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Paciente $paciente) // Route Model Binding
    {
        $reglas = [
            'numero_documento_paciente' => ['sometimes','required','string','max:20',Rule::unique('pacientes', 'numero_documento_paciente')->ignore($paciente->id)],
            'tipo_documento_paciente' => 'sometimes|required|string|max:50',
            'nombres_paciente' => 'sometimes|required|string|max:100',
            'apellidos_paciente' => 'sometimes|required|string|max:100',
            'fecha_nacimiento_paciente' => 'sometimes|required|date_format:Y-m-d',
            'genero_paciente' => 'nullable|string|max:20',
            'direccion_paciente' => 'nullable|string|max:255',
            'telefono_paciente' => 'nullable|string|max:20',
            'email_paciente' => ['nullable','string','email','max:100',Rule::unique('pacientes', 'email_paciente')->ignore($paciente->id)],
            'eps_paciente' => 'nullable|string|max:100',
        ];

        $validador = Validator::make($request->all(), $reglas);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        $paciente->update($request->all());

        // Devolver el paciente actualizado (opcionalmente con relaciones cargadas)
        return response()->json([
            'status' => true,
            'data' => $paciente,
            'message' => 'Paciente actualizado correctamente'
        ]);
    }

    /**
     * Elimina el paciente especificado de la base de datos.
     * @param  \App\Models\Paciente  $paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Paciente $paciente) // Route Model Binding
    {
        try {
            $paciente->delete();
            return response()->json([
                'status' => true,
                'message' => 'Paciente eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            // Loguear el error si es necesario
            Log::error("Error al eliminar paciente ID {$paciente->id}: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el paciente.'
            ], 500); // Error interno del servidor
        }
    }

    /**
     * Muestra los pacientes con más citas.
     * Se puede filtrar por estado de cita y limitar el número de resultados.
     * (Consulta Compuesta #3 - Versión para MySQL)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Muestra los pacientes con más citas. (Consulta Compuesta #3 - Revisada v3)
     */
    public function pacientesConMasCitas(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'limite' => 'nullable|integer|min:1',
            'estado_cita' => 'nullable|string|max:50'
        ]);

        if ($validador->fails()) {
            return response()->json(['status' => false, 'errors' => $validador->errors()], 400);
        }

        $limite = $request->input('limite', 5);
        $estadoCita = $request->input('estado_cita');

        // 1. Obtener IDs y conteos
        $pacientesConteoQuery = DB::table('citas')
            ->select('id_paciente', DB::raw('COUNT(id) as total_citas'));
        if ($estadoCita) {
            $pacientesConteoQuery->where('estado_cita', $estadoCita);
        }
        $pacientesConteo = $pacientesConteoQuery
            ->whereNotNull('id_paciente')
            ->groupBy('id_paciente')
            ->orderBy('total_citas', 'desc')
            ->orderBy('id_paciente') // Añadir un orden secundario estable
            ->take($limite)
            ->pluck('total_citas', 'id_paciente');

        if ($pacientesConteo->isEmpty()) {
            return response()->json(['status' => true, 'data' => [], 'message' => 'No se encontraron pacientes...'], 200);
        }

        // 2. Obtener modelos Paciente
        $pacienteIdsOrdenados = $pacientesConteo->keys()->toArray();

        // ---- INICIO CAMBIO ----
        // Construir la cadena para FIELD manualmente
        if (!empty($pacienteIdsOrdenados)) {
            $pacienteIdsSeguros = array_map('intval', $pacienteIdsOrdenados); // Asegurar enteros
            $fieldOrder = "FIELD(id, " . implode(',', $pacienteIdsSeguros) . ")";

            // Obtener los pacientes usando whereIn y orderByRaw con la cadena construida
            $pacientes = Paciente::whereIn('id', $pacienteIdsSeguros)
                ->orderByRaw($fieldOrder) // Pasar la cadena directamente
                ->get();
        } else {
            // Si no hay IDs, devolver colección vacía para evitar errores
            $pacientes = collect();
        }
        // ---- FIN CAMBIO ----


        // 3. Añadir conteo a modelos
        $pacientesData = $pacientes->map(function ($paciente) use ($pacientesConteo) {
            $pacienteData = $paciente->toArray();
            $pacienteData['total_citas'] = $pacientesConteo[$paciente->id] ?? 0;
            return $pacienteData;
        });

        return response()->json([
            'status' => true,
            'data' => $pacientesData,
            'message' => 'Pacientes con más citas obtenidos correctamente.'
        ]);
    }
}
