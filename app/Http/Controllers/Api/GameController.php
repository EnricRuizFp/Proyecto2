<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Retorna un listado paginado de partidas.
     */
    public function index(Request $request)
    {
        // Puedes incluir relaciones si lo deseas, por ejemplo 'creator'
        $games = Game::with('creator')->paginate(10);
        return response()->json($games);
    }

    public function show($id)
    {
        $game = Game::findOrFail($id);
        return response()->json($game);
    }


    public function store(Request $request)
    {
        $request->validate([
            'is_public' => 'required|boolean'
        ]);

        // Si usas autenticación, asigna el id del usuario autenticado; de lo contrario, se puede dejar nulo
        $userId = auth()->check() ? auth()->id() : null;

        $game = Game::create([
            'creation_date' => now(),
            'is_public'     => $request->is_public,
            'is_finished'   => false,
            'end_date'      => null,
            'created_by'    => $userId,
        ]);

        return response()->json([
            'message' => 'Game created successfully',
            'data'    => $game,
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        // Validación (en este ejemplo, solo se actualiza is_public)
        $request->validate([
            'is_public' => 'required|boolean'
        ]);

        $game->update([
            'is_public' => $request->is_public,
            // Puedes actualizar otros campos si fuese necesario.
        ]);

        return response()->json([
            'message' => 'Game updated successfully',
            'data'    => $game,
        ]);
    }
    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();

        return response()->json([
            'message' => 'Game deleted successfully'
        ]);
    }



    /*

        ///// GAME FUNCTIONS /////

    */

    /**
     * PLAY A PUBLIC GAME
     * This function searches a public game to join. If there are no public games available, it creates a new one.
     * Returns the game you have joined.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function playPublicGame()
    {
        // Generar un usuario random [SUSTITUIR POR EL USUARIO AUTENTICADO]
        $user = [
            'id' => 1,
            'name' => 'Enric',
            'surname1' => 'Ruiz',
            'surname2' => 'Badia',
            'username' => 'enricrb',
            'email' => 'erb@erb.com',
            'nationality' => 'spain'
        ];

        // Buscar todos los juegos públicos que no han comenzado
        $publicUnstartedGames = Game::where('is_public', true)
                                    ->where('is_finished', false)
                                    ->with(['players', 'observers']) // Cargar relaciones de jugadores y observadores
                                    ->withCount('players') // Obtener el count de jugadores
                                    ->get();

        // Para cada juego, revisamos la cantidad de jugadores
        foreach ($publicUnstartedGames as $publicGame) {

            // Obtener la cantidad de jugadores
            $playersCount = $publicGame->players_count;

            // Devolver el primer juego disponible (menos de 2 jugadores)
            if ($playersCount < 2) {

                // ----- Unirse a la partida
                // Aquí es donde aseguramos que todos los datos de la tabla intermedia sean correctos
                $publicGame->players()->attach($user['id'], [
                    'joined' => now() // La fecha de unión del jugador
                ]);

                // Devolver el juego encontrado
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Public free game found, connected successfully',
                    'data'    => $publicGame
                ]);
            }
        }

        // Si no se encontraron juegos públicos, crear un nuevo juego
        $newGame = Game::create([
            'creation_date' => now(),
            'is_public'     => true,
            'is_finished'   => false,
            'end_date'      => null,
            'created_by'    => $user['id']
        ]);

        $newGame->players()->attach($user['id'], [
            'joined' => now() // La fecha de unión del jugador
        ]);

        // Devolver el juego encontrado
        return response()->json([
            'status'  => 'success',
            'message' => 'No games found, new one created and connected successfully',
            'data'    => $newGame
        ]);
    }

    public function createPrivateGame()
    {
        // Generar un usuario random [SUSTITUIR POR EL USUARIO AUTENTICADO]
        $user = [
            'id' => 1,
            'name' => 'Enric',
            'surname1' => 'Ruiz',
            'surname2' => 'Badia',
            'username' => 'enricrb',
            'email' => 'erb@erb.com',
            'nationality' => 'spain'
        ];

        // Crear juego privado
        $newPrivateGame = Game::create([
            'creation_date' => now(),
            'is_public'     => false,
            'is_finished'   => false,
            'end_date'      => null,
            'created_by'    => $user['id']
        ]);

        // Unirse a la partida privada
        $newPrivateGame->players()->attach($user['id'], [
            'joined' => now() // La fecha de unión del jugador
        ]);

        // Devolver el juego creado
        return response()->json([
            'status'  => 'success',
            'message' => 'New private game created and connected successfully',
            'data'    => $newPrivateGame
        ]);

    }

    public function joinPrivateGame($code)
    {

        // Crear un usuario random [SUSTITUIR POR EL USUARIO AUTENTICADO]
        $user = [
            'id' => 1,
            'name' => 'Enric',
            'surname1' => 'Ruiz',
            'surname2' => 'Badia',
            'username' => 'enricrb',
            'email' => 'erb@gmail.com'
        ];


        // Buscar todos los juegos privados que no hay comenzado
        $privateGame = Game::where('code', $code)
                                    ->with(['players', 'observers']) // Cargar relaciones de jugadores y observadores
                                    ->withCount('players') // Obtener el count de jugadores
                                    ->first();
        
        // Si encuentra el juego lo devuelve
        if($privateGame) {

            // Obtener el tipo de partida
            if($privateGame->is_public){

                // Devolver mensaje partida pública
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Game found, but is public.'
                ]);

            }else{

                // Obtener cantidad de jugadores
                $playersCount = $privateGame->players_count;

                if($playersCount > 1){

                    // Si hay más de 1 jugador la partida está llena
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Private game is full.'
                    ]);

                }else{

                    // Unir al usuario a la partida
                    $privateGame->players()->attach($user['id'], [
                        'joined' => now() // La fecha de unión del jugador
                    ]);

                    // Devolver los datos de la partida
                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Private game found & connected.',
                        'data'    => $privateGame
                    ]);
                }
            }

        }else{

            // Partida no encontrada
            return response()->json([
                'status'  => 'error',
                'message' => 'Private game not found.'
            ]);
        }

    }



}
