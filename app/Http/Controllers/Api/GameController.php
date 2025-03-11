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

    public function playFunction(Request $request){

        // Inclusión de los datos pasados por parámetro
        $gameType = $request->input('game');
        $gameCode = $request->input('code');
        $user = $request->input('user');

        /*
            ///// COMRPOBACIONES PRE PARTIDA /////
        */

        // Usuario registrado
        if(!$user){
            return response()->json([
                'status'  => 'failed',
                'message' => 'You are not logged in.'
            ]);
        }

        // Usuario en otra partida
        $unfinishedUserGames = Game::whereIn('id', function ($query) use ($user) {
            $query->select('game_id')
                ->from('game_players')
                ->where('user_id', $user);
        })
        ->where('is_finished', false)
        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Unfinished user games found:',
            'games' => $unfinishedUserGames
        ]);


    }


    /**
     * PLAY A PUBLIC GAME
     * This function searches a public game to join. If there are no public games available, it creates a new one.
     * Returns the game you have joined.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function playPublicGame(Request $request)
    {
        
        // Obtener el usuario del frontend
        $user = $request->input('user');

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

                // Unir al jugador a la partida
                $publicGame->players()->attach($user['id'], ['joined' => now()]);

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

    /**
     * CREATE A PRIVATE GAME
     * This function creates a new private game and connects the user to it.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createPrivateGame(Request $request)
    {
        // Obtener el usuario del frontend
        $user = $request->input('user');

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

    /**
     * JOIN A PRIVATE GAME
     * This function allows a user to join a private game using the game code.
     * If the code does not exist, it returns an error message.
     * If the game is public, it returns an error message.
     * If the game is full, it returns an error message.
     * @param mixed $code
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function joinPrivateGame(Request $request)
    {
        // Obtener el usuario y el código de la request
        $user = $request->input('user');
        $code = $request->input('code');


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
