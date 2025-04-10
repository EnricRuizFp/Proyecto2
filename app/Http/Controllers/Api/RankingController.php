<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    /**
     * Retorna un listado paginado de rankings.
     */
    public function index(Request $request)
    {
        // Incluye la relación 'user' para poder mostrar datos del creador (alias, etc.)
        $rankings = Ranking::with('user')->orderBy('points', 'desc')->paginate(10);
        return response()->json($rankings);
    }

    /**
     * Retorna un listado paginado de rankings para el panel administrativo.
     */
    public function indexAdmin(Request $request)
    {
        $query = Ranking::with('user');

        // Aplicar búsqueda global si existe
        if ($request->has('search_global') && !empty($request->search_global)) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search_global . '%');
            })
                ->orWhere('ranking_id', 'like', '%' . $request->search_global . '%')
                ->orWhere('points', 'like', '%' . $request->search_global . '%');
        }

        // Aplicar ordenamiento
        $orderColumn = $request->get('order_column', 'ranking_id');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderColumn, $orderDirection);

        // Paginar resultados
        $perPage = $request->get('per_page', 10);
        $rankings = $query->paginate($perPage);

        return response()->json($rankings);
    }

    /**
     * Almacena un nuevo ranking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'wins'    => 'required|integer|min:0',
            'losses'  => 'required|integer|min:0',
            'draws'   => 'required|integer|min:0',
            'points'  => 'required|numeric',
        ]);

        $ranking = Ranking::create([
            'user_id'    => $request->user_id,
            'wins'       => $request->wins,
            'losses'     => $request->losses,
            'draws'      => $request->draws,
            'points'     => $request->points,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Ranking created successfully',
            'data'    => $ranking,
        ], 201);
    }

    /**
     * Muestra un ranking en particular.
     */
    public function show($id)
    {
        $ranking = Ranking::findOrFail($id);
        return response()->json($ranking);
    }

    /**
     * Actualiza un ranking existente.
     */
    public function update(Request $request, $id)
    {
        // Buscar el ranking usando la clave primaria (ranking_id)
        $ranking = Ranking::findOrFail($id);

        // Validamos los datos
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'wins'    => 'required|integer|min:0',
            'losses'  => 'required|integer|min:0',
            'draws'   => 'required|integer|min:0',
            'points'  => 'required|numeric',
        ]);

        // Actualizamos los campos
        $ranking->update([
            'user_id'    => $request->user_id,
            'wins'       => $request->wins,
            'losses'     => $request->losses,
            'draws'      => $request->draws,
            'points'     => $request->points,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Ranking updated successfully',
            'data'    => $ranking,
        ]);
    }

    /**
     * Elimina un ranking.
     */
    public function destroy($id)
    {
        $ranking = Ranking::findOrFail($id);
        $ranking->delete();

        return response()->json([
            'message' => 'Ranking deleted successfully',
        ]);
    }

    /**
     * Obtiene la posición global del usuario autenticado
     */
    public function getGlobalPosition(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Obtener todos los rankings ordenados por puntos
            $rankings = Ranking::orderBy('points', 'desc')->get();

            // Encontrar la posición del usuario
            $position = null;
            foreach ($rankings as $index => $ranking) {
                if ($ranking->user_id === $userId) {
                    $position = $index + 1;
                    break;
                }
            }

            return response()->json([
                'status' => 'success',
                'position' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al obtener la posición global'
            ], 500);
        }
    }

    /**
     * Obtiene la posición nacional del usuario autenticado
     */
    public function getNationalPosition(Request $request)
    {
        try {
            $user = $request->user();
            $userNationality = $user->nationality;

            // Obtener rankings de usuarios con la misma nacionalidad
            $rankings = Ranking::with('user')
                ->whereHas('user', function ($query) use ($userNationality) {
                    $query->where('nationality', $userNationality);
                })
                ->orderBy('points', 'desc')
                ->get();

            // Encontrar la posición del usuario
            $position = null;
            foreach ($rankings as $index => $ranking) {
                if ($ranking->user_id === $user->id) {
                    $position = $index + 1;
                    break;
                }
            }

            return response()->json([
                'status' => 'success',
                'position' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al obtener la posición nacional'
            ], 500);
        }
    }

    /**
     * Obtiene los puntos del usuario autenticado
     */
    public function getUserPoints(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $ranking = Ranking::where('user_id', $userId)->first();

            return response()->json([
                'status' => 'success',
                'points' => $ranking ? $ranking->points : 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al obtener los puntos del usuario'
            ], 500);
        }
    }

    /**
     * Retorna un listado de rankings filtrado por nacionalidad
     */
    public function getNationalRanking(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $userNationality = $user->nationality;

            if (empty($userNationality)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El usuario no tiene una nacionalidad asignada'
                ], 400);
            }

            // Obtener rankings de usuarios con la misma nacionalidad
            $rankings = Ranking::with('user')
                ->whereHas('user', function ($query) use ($userNationality) {
                    $query->where('nationality', $userNationality);
                })
                ->orderBy('points', 'desc')
                ->take($limit)
                ->get();

            // Preparar respuesta con datos adicionales para debugging
            return response()->json([
                'status' => 'success',
                'user_nationality' => $userNationality,
                'data' => $rankings,
                'count' => $rankings->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el ranking nacional: ' . $e->getMessage()
            ], 500);
        }
    }
}
