<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Models\DisponibilidadMedico;
use Illuminate\Database\QueryException;


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
$roleId = 3;

// Crear nuevo usuario
$user = User::create([
    'name'      => $request->name,
    'lastname'  => $request->lastname,
    'rut'       => $request->rut,
    'phone'     => $request->phone,
    'email'     => $request->email,
    'password'  => bcrypt($request->password),
    'role_id'   => $roleId,
    'enable'    => true,
]);

//Usuario registrado con éxito
        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado con exito',
            'data' => $user,
        ], 201);

}
// Metodo para registrar doctor
public function registerDoctor(Request $request)
{
    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'name'       => 'required|string|min:3|max:255',
        'lastname'   => 'required|string|min:3|max:255',
        'profession' => 'required|string|max:255',
        'rut'        => 'required|string|min:9|max:10|unique:users',
        'phone'      => 'required|string|size:12',
        'email'      => 'required|string|email|max:255|unique:users',
    ]);
      // Si la validacion falla, devolver errores
    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error de validacion',
            'errors'  => $validator->errors(),
        ], 422);
    }
       // Validar que el RUT sea correcto 
    $rutValidation = $this->phpRule_ValidarRut($request->rut);
    if ($rutValidation['error']) {
        return response()->json([
            'status'  => 'error',
            'message' => $rutValidation['msj'],
        ], 422);
    }

    // Generar una contrasena aleatoria de 10 caracteres
    $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);

    // Crear el usuario doctor con los datos recibidos
    $doctor = User::create([
        'name'       => $request->name,
        'lastname'   => $request->lastname,
        'rut'        => $request->rut,
        'phone'      => $request->phone,
        'email'      => $request->email,
        'profession' => $request->profession,
        'password'   => bcrypt($randomPassword),
        'role_id'    => 3, // rol id de medico 
        'enable'     => true,
    ]);

    // Enviar correo al doctor con su contrasena generada

    Mail::send('emails.doctor_registered', [
        'user'     => $doctor,
        'password' => $randomPassword,
    ], function ($message) use ($doctor) {
        $message->to($doctor->email);
        $message->subject('Registro de Cuenta de Medico');
    });

    // Respuesta exitosa con los datos del doctor creado
    
    return response()->json([
        'status'  => 'success',
        'message' => 'Medico registrado correctamente. Contraseña enviada por correo.',
        'data'    => $doctor,
    ], 201);
}


// Metodo para validar rut
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
        'password' => 'required|string|min:6',  // Cambié 'size:6' por 'min:6' para mayor flexibilidad
    ]);

    // Si la validación falla, devolver un error
    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error de validación',  // Mantener mensajes en español
            'errors'  => $validator->errors(),
        ], 422);
    }

    // Obtener las credenciales del request
    $credentials = $request->only('email', 'password');

    try {
        // Verificar primero si el usuario existe y está habilitado
        $user = User::where('email', $request->email)->first();
        
        // Si el usuario no existe, devolver error de credenciales inválidas
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales inválidas',
            ], 401);
        }
        
        // Verificar si el usuario está habilitado ANTES de intentar autenticar
        if ($user->enabled === 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Su cuenta ha sido deshabilitada. Contacte al administrador.',
            ], 403);
        }
    
        // Intentar autenticar y generar token JWT
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales inválidas',
            ], 401);
        }
        
        // Obtener usuario autenticado (ya verificamos que está habilitado)
        $user = JWTAuth::user();

        // Respuesta exitosa con token y datos del usuario
        return response()->json([
            'status'  => 'success',
            'message' => 'Inicio de sesión exitoso',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ], 200);

    } catch (\Exception $e) { 
        // Captura de errores inesperados
        return response()->json([
            'status'  => 'error',
            'message' => 'Error al iniciar sesión',
            'errors'  => $e->getMessage(),
        ], 500);
    }
}

    // Envío de email con link de recuperación de contraseña
    public function forgotPassword(Request $request){
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);

        // Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Obtener el email del request
        $email = $request->input('email');

        // Verificar si el usuario existe
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        // Token con expiracion en 5 minutos
        $token = JWTAuth::customClaims([
            'email' => $user->email,
            'exp'   => Carbon::now()->addMinutes(10)->timestamp,
        ])->FromUser($user);

        // Generar link de recuperación
        $resetLink = 'http://localhost:4200/reset-password';

        // Enviar email (con vista Blade) -> Blade es un motor de plantillas de Laravel
        Mail::send('emails.forgot_password', [
            'user' => $user,
            'resetLink' => $resetLink
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Restablecer contraseña');
        });

        // Respuesta exitosa
        return response()->json([
            'status'  => 'Respuesta exitosa',
            'message' => 'Correo de recuperación enviado correctamente',
        ], 200);
    }

    // Cambiar contraseña usando token
    public function resetPassword(Request $request){
        // Validar los datos de entrada
        $request->validate([
            'email'    => 'required|string|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($request->input('email') != $request->input('email')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El email no coincide con el correo ingresado',
            ], 422);
        }

        try {
            // Verificar el token
            $token = $request->input('token');
            $payload = JWTAuth::setToken($token)->getPayload();

            // Obtener el email del payload
            $email = $payload->get('email');

            // Verificar si el usuario existe
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // Respuesta exitosa
            return response()->json([
                'status'  => 'Respuesta exitosa',
                'message' => 'Contraseña restablecida correctamente',
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El enlace ha expirado',
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al restablecer la contraseña',
                'errors'  => $e->getMessage(),
            ], 500);
        }
}

//Metodo para obtener la lista de usuarios
// Este método devuelve una lista de usuarios (clientes y médicos) con advertencia si no hay clientes
// Solo los administradores pueden acceder a esta ruta
// Devuelve un JSON con el estado, mensaje y datos de los usuarios

public function listUsers() {
    // Verificar si existen pacientes (role_id = 2)
    $pacientesExisten = User::where('role_id', 2)->exists();

    // Obtener todos los usuarios que son pacientes (role_id = 2) o médicos (role_id = 3)
    // Excluye a los administradores (role_id = 1)
    $users = User::whereIn('role_id', [2, 3])
                ->select('id', 'name', 'lastname', 'rut', 'phone', 'email', 'enabled', 'role_id')
                ->get();

    // Respuesta con advertencia si no hay pacientes
    return response()->json([
        'status'  => 'success',
        'message' => $pacientesExisten ? null : 'Advertencia: No hay pacientes para mostrar.',
        'data'    => $users
    ]);
}


//revisar
     public function toggleUserStatus($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    if ($user->role_id === 1) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No se puede modificar el estado de un administrador'
        ], 403);
    }

    $user->enabled = !$user->enabled;
    $user->save();

    return response()->json([
        'status'  => 'success',
        'message' => 'Estado de la cuenta actualizado correctamente',
        'data'    => [
            'id'      => $user->id,
            'enabled' => $user->enabled
        ]
    ]);
}

//revisar
public function updateUser(Request $request, $id)
{
  try {
        $authUser = JWTAuth::parseToken()->authenticate();
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token expirado, por favor inicia sesión de nuevo',
        ], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token inválido',
        ], 401);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'No autenticado',
        ], 401);
    }

    // Solo admins (role_id = 1) o el mismo usuario pueden modificar
    if ($authUser->role_id !== 1 && $authUser->id != $id) {
        return response()->json([
            'status' => 'error',
            'message' => 'No tienes permisos para modificar este usuario'
        ], 403);
    }

    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'name'     => 'sometimes|required|string|min:3|max:255',
        'lastname' => 'sometimes|required|string|min:3|max:255',
        'phone'    => 'sometimes|required|string|size:12',
        'email'    => "sometimes|required|email|max:255|unique:users,email,$id",
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422);
    }


    $user->update($request->only(['name', 'lastname', 'phone', 'email']));

    return response()->json([
        'status'  => 'success',
        'message' => 'Usuario actualizado correctamente',
        'data'    => $user
    ]);
}

public function updateMe(Request $request)
{
    try {
        $authUser = JWTAuth::parseToken()->authenticate();
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token expirado, por favor inicia sesión de nuevo',
        ], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token inválido',
        ], 401);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'No autenticado',
        ], 401);
    }

    $id = $authUser->id;

    $validator = Validator::make($request->all(), [
        'name'     => 'sometimes|required|string|min:3|max:255',
        'lastname' => 'sometimes|required|string|min:3|max:255',
        'phone'    => 'sometimes|required|string|size:12',
        'email'    => "sometimes|required|email|max:255|unique:users,email,$id",
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Errores de validación',
            'errors'  => $validator->errors()
        ], 422);
    }

    

    $authUser->update($request->only(['name', 'lastname', 'phone', 'email']));

    return response()->json([
        'status'  => 'success',
        'message' => 'Tu perfil fue actualizado correctamente',
        'data'    => $authUser
    ]);
}


public function me(Request $request)
{
    return response()->json([
        'user' => $request->user()
    ]);
}

}
