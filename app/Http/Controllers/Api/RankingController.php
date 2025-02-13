<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    /**
     * Retorna un listado paginado de rankings.
     */
    public function index(Request $request)
    {
        // Incluye la relación 'user' para poder mostrar datos del creador (alias, etc.)
        $rankings = Ranking::with('user')->paginate(10);
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
        $ranking = Ranking::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'wins'    => 'required|integer|min:0',
            'losses'  => 'required|integer|min:0',
            'draws'   => 'required|integer|min:0',
            'points'  => 'required|numeric',
        ]);

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
}
