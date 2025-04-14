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
}
