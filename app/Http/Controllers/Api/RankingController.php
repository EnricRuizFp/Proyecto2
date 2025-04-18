<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Asegúrate de importar Log
use Illuminate\Support\Facades\DB; // Import DB facade

class RankingController extends Controller
{
    /**
     * LISTAR RANKINGS
     * 
     * Devuelve un listado paginado de todos los rankings ordenados por puntos.
     * 
     * Sin parámetros de entrada.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Listado paginado en formato JSON.
     */
    public function index(Request $request)
    {
        // Incluye la relación 'user' para poder mostrar datos del creador (alias, etc.)
        // Asegura que la paginación por defecto sea de 10 elementos
        $rankings = Ranking::with('user')->orderBy('points', 'desc')->paginate(10);
        return response()->json($rankings);
    }

    /**
     * LISTAR TODOS LOS RANKINGS
     * 
     * Devuelve un listado de todos los rankings ordenados por puntos.
     * 
     * Sin parámetros de entrada.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Listado en formato JSON.
     */
    public function getAllRankings()
    {
        try {
            $rankings = Ranking::with('user')
                ->orderBy('points', 'desc')
                ->get();

            return response()->json($rankings);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving rankings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LISTAR RANKINGS ADMINISTRADOR
     * 
     * Retorna un listado paginado de rankings para el panel de admin.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request: (opcionales todos)
     * {
     *   "search_global": string,
     *   "order_column": string (ranking_id|points|wins|losses|draws|updated_at),
     *   "order_direction": "asc"|"desc",
     *   "per_page": int
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Lista paginada y filtrada de rankings en formato JSON.
     */
    public function indexAdmin(Request $request)
    {
        $query = Ranking::with('user');

        // Aplicar búsqueda global si existe
        if ($request->has('search_global') && !empty($request->search_global)) {
            // Agrupar las condiciones OR para la búsqueda
            $query->where(function ($subQuery) use ($request) {
                $subQuery->whereHas('user', function ($q) use ($request) {
                    $q->where('username', 'like', '%' . $request->search_global . '%');
                })
                    ->orWhere('ranking_id', 'like', '%' . $request->search_global . '%')
                    ->orWhere('points', 'like', '%' . $request->search_global . '%');
            });
        }

        // Aplicar ordenamiento
        $orderColumn = $request->get('order_column', 'ranking_id');
        $orderDirection = $request->get('order_direction', 'desc');
        // Validar columnas permitidas para ordenar (ejemplo)
        $allowedSortColumns = ['ranking_id', 'points', 'wins', 'losses', 'draws', 'updated_at'];
        if (in_array($orderColumn, $allowedSortColumns)) {
            // Si la columna es 'username', necesitamos ordenar por la tabla relacionada
            if ($orderColumn === 'username') {
                $query->orderBy($orderColumn, $orderDirection);
            } else {
                $query->orderBy($orderColumn, $orderDirection);
            }
        } else {
            $query->orderBy('ranking_id', 'desc'); // Fallback a un default seguro
        }


        // Paginar resultados - Asegura que el default sea 10
        $perPage = $request->get('per_page', 10);
        $rankings = $query->paginate($perPage);

        return response()->json($rankings);
    }

    /**
     * CREAR RANKING
     * 
     * Crea un nuevo registro de ranking.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user_id": int|required|unique,
     *   "wins": int|required|min:0,
     *   "losses": int|required|min:0,
     *   "draws": int|required|min:0,
     *   "points": numeric|required
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id|unique:rankings,user_id', // Añadido unique si cada usuario solo debe tener un ranking
                'wins'    => 'required|integer|min:0',
                'losses'  => 'required|integer|min:0',
                'draws'   => 'required|integer|min:0',
                'points'  => 'required|numeric',
            ]);

            // Crear el ranking
            $ranking = Ranking::create([
                'user_id'    => $validatedData['user_id'],
                'wins'       => $validatedData['wins'],
                'losses'     => $validatedData['losses'],
                'draws'      => $validatedData['draws'],
                'points'     => $validatedData['points'],
            ]);

            return response()->json([
                'message' => 'Ranking created successfully',
                'data'    => $ranking,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create ranking', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * MOSTRAR RANKING
     * 
     * Devuelve los datos del ranking pasado por parámetro.
     * 
     * @param mixed $id
     * Datos esperados: ID del ranking a mostrar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Datos del ránking en formato JSON.
     */
    public function show($id)
    {
        $ranking = Ranking::where('user_id', $id)->firstOrFail();
        return response()->json($ranking);
    }

    /**
     * ACTUALIZAR RANKING
     * 
     * Actualiza los datos del ranking especificado por parámetro.
     * 
     * @param mixed $id
     * Datos esperados: ID del ranking a actualizar.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user_id": int|required|unique,
     *   "wins": int|required|min:0,
     *   "losses": int|required|min:0,
     *   "draws": int|required|min:0,
     *   "points": numeric|required
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Datos del ránking actualizado en formato JSON.
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
     * ELIMINAR RANKING
     * 
     * Elimina el ranking especificado por parámetro.
     * 
     * @param mixed $id
     * Datos esperados: ID del ranking a eliminar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Mensaje en formato JSON.
     */
    public function destroy($id)
    {
        $ranking = Ranking::findOrFail($id);
        $ranking->delete();

        return response()->json([
            'message' => 'Ranking deleted successfully',
        ]);
    }

    /*
     
      /////   PETICIONES COMPLEJAS /////

    */


    /**
     * GET USER POINTS
     * 
     * Devuelve los puntos del usuario especificado.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y puntos en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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
     * GET GLOBAL POSITION
     * 
     * Devuelve la posición global del usuario especificado.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y posición gobal en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getGlobalPosition(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $userPoints = Ranking::where('user_id', $userId)->value('points');

            if ($userPoints === null) {
                return response()->json(['status' => 'success', 'position' => null]); // User might not have a ranking yet
            }

            // Count users with more points + 1 for the rank
            $position = Ranking::where('points', '>', $userPoints)->count() + 1;

            return response()->json(['status' => 'success', 'position' => $position]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error getting global position'], 500);
        }
    }

    /**
     * GET NATIONAL POSITION
     * 
     * Devuelve la posición nacional del usuario especificado.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *     "nationality": string|required
     *   }
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y posición nacional en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getNationalPosition(Request $request)
    {
        try {
            $user = $request->user();
            $userId = $user->id;
            $nationality = $user->nationality; // Assuming user model has 'nationality'

            if (!$nationality) {
                return response()->json(['status' => 'failed', 'message' => 'User nationality not set'], 400);
            }

            $userPoints = Ranking::where('user_id', $userId)->value('points');

            if ($userPoints === null) {
                // User might not have a ranking yet, position is effectively undefined or last
                // Return null or calculate based on all users in the country
                return response()->json(['status' => 'success', 'position' => null]);
            }

            // Count users from the same country with more points + 1
            $position = Ranking::whereHas('user', function ($query) use ($nationality) {
                $query->where('nationality', $nationality);
            })
                ->where('points', '>', $userPoints)
                ->count() + 1; // Add 1 to get the rank

            return response()->json(['status' => 'success', 'position' => $position]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error getting national position'], 500);
        }
    }

    /**
     * GET NATIONAL RANKING
     * 
     * Devuelve el ránking nacional a partir de la nacionalidad del usuario
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "nationality": string|required
     *   }
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito, nacionalidad y ránking nacional en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getNationalRanking(Request $request)
    {
        try {
            $user = $request->user();
            $nationality = $user->nationality;

            // Devolver error si el usuario no tiene nacionalidad
            if (!$nationality) {
                return response()->json(['status' => 'failed', 'message' => 'User nationality not set to view national rankings.'], 400);
            }

            // Límite por defecto de 10 ránkings
            $limit = $request->query('limit', 10);

            // Obtener rankings
            $rankings = Ranking::with('user:id,username,nationality')
                ->whereHas('user', function ($query) use ($nationality) {
                    $query->where('nationality', $nationality);
                })
                ->orderBy('points', 'desc')
                ->take($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $rankings,
                'user_nationality' => $nationality
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error getting national ranking list'], 500);
        }
    }
}
