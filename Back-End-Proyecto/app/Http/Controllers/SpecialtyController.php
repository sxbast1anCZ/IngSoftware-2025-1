<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\JsonResponse;



//Esta clase es más para ayudar a desplegar cosas en el front-end relacionado a la especialidad de los métodos.


class SpecialtyController extends Controller
{
    public function index(): JsonResponse
    {
        $specialties = Specialty::select('id', 'name')->orderBy('name')->get();

        return response()->json($specialties);
    }

        public function obtenerEspecialidadesMedicos(): JsonResponse
    {
    try {
        $especialidades = Specialty::whereHas('users', function ($query) {
                $query->whereNotNull('specialty_id')
                      ->where('role_id', 2); // solo médicos
            })
            ->with(['users' => function ($query) {
                $query->whereNotNull('specialty_id')
                      ->where('role_id', 2)
                      ->select('id', 'name', 'lastname', 'specialty_id');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($specialty) {
                return [
                    'specialty_id' => $specialty->id,
                    'name' => $specialty->name,
                    'users' => $specialty->users->map(function ($user) {
                        return [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'lastname' => $user->lastname,
                            'specialty_id' => $user->specialty_id,
                        ];
                    }),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $especialidades
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener especialidades',
            'error' => $e->getMessage()
        ], 500);
    }
    }

/**
 * Retorna la lista completa de especialidades médicas
 * 
 * Este método devuelve todas las especialidades disponibles para ser mostradas
 * en el front-end, independientemente de si tienen médicos asociados o no.
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getAllSpecialties(): JsonResponse
{
    try {
        $specialties = Specialty::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $specialties
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener las especialidades',
            'error' => $e->getMessage()
        ], 500);
    }
}





}
