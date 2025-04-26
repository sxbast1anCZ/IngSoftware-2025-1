<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    



   // Metodo de register
public function register (Request $request){
    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|min:3|max:255',
        'lastname'  => 'required|string|min:3|max:255',
        'rut'       => 'required|string|min:9|max:9|unique:users',
        'phone'     => 'required|string|size:12',
        'email'     => 'required|string|email|max:255|unique:users',
        'password'  => 'required|string|min:6|confirmed',
    ]);
    
    // Si la validación falla, devolver un error

if ($validator->fails()) {
    return response()->json([
        'status'  => 'error',
        'message' => 'Validation error',
        'errors'  => $validator->errors(),
    ], 422);
}

// Asignar rol por defecto (2 = paciente)
$roleId = 2;

// Crear nuevo usuario
$user = User::create([
    'name'      => $request->name,
    'lastname'  => $request->lastname,
    'rut'       => $request->rut,
    'phone'     => $request->phone,
    'email'     => $request->email,
    'password'  => bcrypt($request->password),
    'role_id'   => $roleId,
]);

//Usuario registrado con éxito
        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado con exito',
            'data' => $user,
        ], 201);


}

    //Metodo de login

    public function login(Request $request)
{
    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'email'    => 'required|string|email|max:255',
        'password' => 'required|string|size:6',
    ]);

    // Si la validación falla, devolver un error
    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }

    // Obtener las credenciales del request
    $credentials = $request->only('email', 'password');

    try {
        // Intentar autenticar y generar token JWT
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }
    
    // Obtener usuario autenticado
    $user = JWTAuth::user();

    // Respuesta exitosa con token y datos del usuario
    return response()->json([
        'status'  => 'success',
        'message' => 'Login successful',
        'data'    => [
            'user'  => $user,
            'token' => $token,
        ],
    ], 200);

    } catch (\Exception $e) { 
    
    // Captura de errores inesperados
    return response()->json([
        'status'  => 'error',
        'message' => 'Login failed',
        'errors'  => $e->getMessage(),
    ], 500);
        }
    }


}














