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
     * Retorna un listado paginado de rankings.
     */
    public function index(Request $request)
    {
        // Incluye la relación 'user' para poder mostrar datos del creador (alias, etc.)
        // Asegura que la paginación por defecto sea de 10 elementos
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
     * Almacena un nuevo ranking.
     */
    public function store(Request $request)
    {
        Log::info('[RankingController@store] Received request data:', $request->all()); // Log incoming data

        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id|unique:rankings,user_id', // Añadido unique si cada usuario solo debe tener un ranking
                'wins'    => 'required|integer|min:0',
                'losses'  => 'required|integer|min:0',
                'draws'   => 'required|integer|min:0',
                'points'  => 'required|numeric',
            ]);

            Log::info('[RankingController@store] Validation passed. Data:', $validatedData); // Log validated data

            $ranking = Ranking::create([
                'user_id'    => $validatedData['user_id'],
                'wins'       => $validatedData['wins'],
                'losses'     => $validatedData['losses'],
                'draws'      => $validatedData['draws'],
                'points'     => $validatedData['points'],
                // 'updated_at' => now(), // 'created_at' y 'updated_at' suelen ser manejados automáticamente por Eloquent
            ]);

            Log::info('[RankingController@store] Ranking created successfully. ID:', ['ranking_id' => $ranking->ranking_id ?? null, 'user_id' => $ranking->user_id]); // Log success

            return response()->json([
                'message' => 'Ranking created successfully',
                'data'    => $ranking,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[RankingController@store] Validation failed:', ['errors' => $e->errors()]); // Log validation errors
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('[RankingController@store] Exception during creation:', [ // Log any other exceptions
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Optional: for detailed debugging
            ]);
            return response()->json(['message' => 'Failed to create ranking', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un ranking en particular basado en el user_id.
     */
    public function show($id)
    {
        $ranking = Ranking::where('user_id', $id)->firstOrFail();
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
     * Obtiene la posición global del usuario autenticado.
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
            Log::error('[RankingController@getGlobalPosition] Error:', ['message' => $e->getMessage()]);
            return response()->json(['status' => 'failed', 'message' => 'Error getting global position'], 500);
        }
    }

    /**
     * Obtiene la posición nacional del usuario autenticado.
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
            Log::error('[RankingController@getNationalPosition] Error:', ['message' => $e->getMessage()]);
            return response()->json(['status' => 'failed', 'message' => 'Error getting national position'], 500);
        }
    }

    /**
     * Obtiene el listado del ranking nacional paginado.
     */
    public function getNationalRankingList(Request $request)
    {
        try {
            $user = $request->user();
            $nationality = $user->nationality;

            if (!$nationality) {
                // If the user must have a nationality to view national rankings
                return response()->json(['status' => 'failed', 'message' => 'User nationality not set to view national rankings.'], 400);
                // Alternatively, allow viewing even without nationality, but the concept might be odd.
            }

            $limit = $request->query('limit', 10); // Default limit to 10 if not provided

            $rankings = Ranking::with('user:id,username,nationality') // Eager load user data, select only needed fields
                ->whereHas('user', function ($query) use ($nationality) {
                    $query->where('nationality', $nationality);
                })
                ->orderBy('points', 'desc')
                ->take($limit) // Use take() instead of paginate() if you only need the top N
                ->get();

            // If you need pagination instead of just top N:
            // $rankings = Ranking::with('user:id,username,nationality')
            //     ->whereHas('user', function ($query) use ($nationality) {
            //         $query->where('nationality', $nationality);
            //     })
            //     ->orderBy('points', 'desc')
            //     ->paginate($limit); // Use paginate()

            return response()->json([
                'status' => 'success',
                'data' => $rankings,
                'user_nationality' => $nationality // Send back the nationality used for the filter
            ]);
        } catch (\Exception $e) {
            Log::error('[RankingController@getNationalRankingList] Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Include trace for debugging
            ]);
            return response()->json(['status' => 'failed', 'message' => 'Error getting national ranking list'], 500);
        }
    }
}
