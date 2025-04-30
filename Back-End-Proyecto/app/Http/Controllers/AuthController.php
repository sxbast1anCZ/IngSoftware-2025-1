<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    
   // Metodo de register
public function register (Request $request){
    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|min:3|max:255',
        'lastname'  => 'required|string|min:3|max:255',
        'rut'       => 'required|string|min:9|max:10|unique:users',
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

// Validar RUT con la función personalizada
$rutValidation = $this->phpRule_ValidarRut($request->rut);
if ($rutValidation['error']) {
    return response()->json([
        'status'  => 'error',
        'message' => $rutValidation['msj'],
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

public function phpRule_ValidarRut($rut) {

    // Verifica que no esté vacio y que el string sea de tamaño mayor a 3 carácteres(1-9)        
    if ((empty($rut)) || strlen($rut) < 3) {
        return array('error' => true, 'msj' => 'RUT vacío o con menos de 3 caracteres.');
    }

    // Quitar los últimos 2 valores (el guión y el dígito verificador) y luego verificar que sólo sea
    // numérico
    $parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

    if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
        return array('error' => true, 'msj' => 'La parte numérica del RUT sólo debe contener números.');
    }

    $guionYVerificador = substr($rut, -2, 2);
    // Verifica que el guion y dígito verificador tengan un largo de 2.
    if (strlen($guionYVerificador) != 2) {
        return array('error' => true, 'msj' => 'Error en el largo del dígito verificador.');
    }

    // obliga a que el dígito verificador tenga la forma -[0-9] o -[kK]
    if (!preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
        return array('error' => true, 'msj' => 'El dígito verificador no cuenta con el patrón requerido');
    }

    // Valida que sólo sean números, excepto el último dígito que pueda ser k
    if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
        return array('error' => true, 'msj' => 'Error al digitar el RUT');
    }

    $rutV = preg_replace('/[\.\-]/i', '', $rut);
    $dv = substr($rutV, -1);
    $numero = substr($rutV, 0, strlen($rutV) - 1);
    $i = 2;
    $suma = 0;
    foreach (array_reverse(str_split($numero)) as $v) {
        if ($i == 8) {
            $i = 2;
        }
        $suma += $v * $i;
        ++$i;
    }
    $dvr = 11 - ($suma % 11);
    if ($dvr == 11) {
        $dvr = 0;
    }
    if ($dvr == 10) {
        $dvr = 'K';
    }
    if ($dvr == strtoupper($dv)) {
        return array('error' => false, 'msj' => 'RUT ingresado correctamente.');
    } else {
        return array('error' => true, 'msj' => 'El RUT ingresado no es válido.');
    }
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














