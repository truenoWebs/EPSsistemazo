<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario; // Asegúrate que el modelo se llame Usuario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Para reglas de validación más complejas

class UsuarioController extends Controller
{
    /**
     * Muestra una lista de los usuarios.
     */
    public function index(Request $request)
    {
        // Implementar paginación y filtros si es necesario
        $usuarios = Usuario::with(['paciente', 'medico'])->get(); // Cargar relaciones

        return response()->json([
            'status' => true,
            'data' => $usuarios,
            'message' => 'Lista de usuarios obtenida correctamente'
        ]);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $reglas = [
            'nombre_usuario' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email', // 'usuarios' es el nombre de tu tabla
            'contrasena' => 'required|string|min:8', // 'password' es el campo en la request, 'contrasena' en BD
            'rol_usuario' => ['required', 'string', Rule::in(['paciente', 'medico', 'administrador'])],
            'paciente_id' => 'nullable|exists:pacientes,id', // Valida que el paciente_id exista en la tabla pacientes
            'medico_id' => 'nullable|exists:medicos,id',     // Valida que el medico_id exista en la tabla medicos
        ];

        $mensajes = [
            'nombre_usuario.required' => 'El nombre del usuario es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol_usuario.required' => 'El rol del usuario es obligatorio.',
            'rol_usuario.in' => 'El rol seleccionado no es válido.',
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'medico_id.exists' => 'El médico seleccionado no existe.',
        ];

        $validador = Validator::make($request->all(), $reglas, $mensajes);

        if ($validador->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validador->errors()
            ], 400);
        }

        $datosUsuario = $request->only(['nombre_usuario', 'email', 'rol_usuario', 'paciente_id', 'medico_id']);
        $datosUsuario['contrasena'] = Hash::make($request->input('contrasena')); // Hashear la contraseña

        $usuario = Usuario::create($datosUsuario);

        return response()->json([
            'status' => true,
            'data' => $usuario,
            'message' => 'Usuario creado correctamente'
        ], 201);
    }

    /**
     * Muestra el usuario especificado.
     */
    public function show(Usuario $usuario) // Route Model Binding
    {
        // Cargar relaciones si no se hizo globalmente o si se necesitan específicas aquí
        $usuario->load(['paciente', 'medico']);
        return response()->json([
            'status' => true,
            'data' => $usuario,
            'message' => 'Usuario encontrado'
        ]);
    }

    /**
     * Actualiza el usuario especificado en la base de datos.
     */
    public function update(Request $request, Usuario $usuario) // Route Model Binding
    {
        $reglas = [
            'nombre_usuario' => 'sometimes|required|string|max:255',
            'email' => ['sometimes','required','string','email','max:255',Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'contrasena' => 'sometimes|nullable|string|min:8', // Solo actualizar si se provee
            'rol_usuario' => ['sometimes','required','string',Rule::in(['paciente', 'medico', 'administrador'])],
            'paciente_id' => 'nullable|exists:pacientes,id',
            'medico_id' => 'nullable|exists:medicos,id',
        ];
        // Mensajes de error personalizados (opcional, pero recomendado)
        $mensajes = [
            'nombre_usuario.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol_usuario.required' => 'El rol es obligatorio.',
            'rol_usuario.in' => 'El rol seleccionado no es válido.',
        ];


        $validador = Validator::make($request->all(), $reglas, $mensajes);

        if ($validador->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validador->errors()
            ], 400);
        }

        $datosActualizar = $request->only(['nombre_usuario', 'email', 'rol_usuario', 'paciente_id', 'medico_id']);

        if ($request->filled('contrasena')) { // 'filled' verifica que no sea null ni string vacío
            $datosActualizar['contrasena'] = Hash::make($request->input('contrasena'));
        }

        $usuario->update($datosActualizar);

        return response()->json([
            'status' => true,
            'data' => $usuario,
            'message' => 'Usuario actualizado correctamente'
        ]);
    }

    /**
     * Elimina el usuario especificado de la base de datos.
     */
    public function destroy(Usuario $usuario) // Route Model Binding
    {
        // Considerar políticas de eliminación (soft delete, etc.)
        $usuario->delete();
        return response()->json([
            'status' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
