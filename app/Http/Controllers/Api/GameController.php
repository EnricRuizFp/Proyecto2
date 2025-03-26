<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * CHECK USER REQUIREMENTS
     * Esta función comprueba si el usuario cumple con los requisitos para jugar una partida.
     * 
     * 
     * Summary of checkUserRequirements
     * @param \Illuminate\Http\Request $request
     *
     * Summary of checkUserRequirements
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function checkUserRequirements(Request $request){

        // Inclusión de los datos pasados por parámetro
        $gameType = $request->input('gameType');
        $gameCode = $request->input('gameCode');
        $user = $request->input('user');

        /*
            ///// COMPROBACIONES PRE PARTIDA /////
        */

        // Usuario registrado - Validación más estricta
        if(!$user || !isset($user['id']) || !$user['username']) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'You must be logged in to play.',
                'game' => null
            ]);
        }

        // Usuario en otra partida
        $unfinishedUserGames = Game::whereIn('id', function ($query) use ($user) {$query->select('game_id')->from('game_players')->where('user_id', $user);})->where('is_finished', false)->get();
        if(count($unfinishedUserGames) > 0){

            // Terminar todas las posibles partidas que tenga el usuario
            foreach ($unfinishedUserGames as $game) {

                // Obtener los jugadores de la partida
                $players = GamePlayer::where('game_id', $game->id)->get();
    
                // Si hay dos jugadores, asignar el ganador, si hay 1, dejar el ganador como null
                if ($players->count() == 2) {
                    // El ganador es el otro jugador
                    $winner = $players->first()->user_id == $user ? $players->last()->user_id : $players->first()->user_id;
                }else{
                    $winner = null;
                }
    
                // Marcar la partida como finalizada
                $game->update([
                    'is_finished' => true,
                    'end_date'    => now(),
                    'winner'      => $winner
                ]);
            }

            return response()->json([
                'status'  => 'failed',
                'message' => 'Your user is leaving the game. Wait a few seconds.',
                'game' => null
            ]);
        }

        // Response OK
        return response()->json([
            'status' => 'success',
            'message' => 'User ready to play a game'
        ]);
    }

    /**
     * FIND MATCH FUNCTION
     * Esta función tiene diversas salidas.
     * Partida pública: Si no hay partidas públicas disponibles, crea una nueva y conecta al usuario.
     *                 Si hay partidas públicas disponibles, conecta al usuario a la primera que encuentre.
     * 
     * Partida privada: Si no se pasa el código, crea una nueva partida privada y conecta al usuario.
     *                 Si se pasa el código, intenta unirse a la partida privada.
     * 
     *
     * Summary of findMatchFunction
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function findMatchFunction(Request $request)
    {
        // Validar los datos recibidos y settearlos en variables separadas
        $request->validate([
            'gameType' => 'required|string|in:public,private',
            'user' => 'required|array',
            'user.id' => 'required|integer',
            'gameCode' => 'nullable|string'
        ]);
        $gameType = $request->input('gameType');
        $gameCode = $request->input('gameCode');
        $user = $request->input('user');

        // Función según tipo
        if ($gameType === "public") {

            // Unirse a una partida pública
            $response = $this->playPublicGame($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Joined to a game',
                'game' =>  $response->getData()->data
            ]);

        } else if ($gameType === "private") {

            if ($gameCode == "null") {

                // Crear una partida privada
                $response = $this->createPrivateGame(new Request(['user' => $user]));
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Creando partida privada.',
                    'game' => $response->getData()->data
                ]);
            }

            // Unirse a una partida privada
            $response = $this->joinPrivateGame(new Request(['user' => $user, 'code' => $gameCode]));
            if ($response->getData()->status === 'failed') {
                return response()->json([
                    'status' => 'failed',
                    'message' => $response->getData()->message,
                    'game' => null
                ]);
            }
            return response()->json([
                'status'  => 'success',
                'message' => 'Entrando a partida privada.',
                'game' => $response->getData()
            ]);
        }

        // Tipo de juego no válido
        return response()->json([
            'status'  => 'failed',
            'message' => 'Tipo de juego no válido'
        ]);
    }

    public function finishMatchFunction(Request $request)
    {
        $gameCode = $request->input('gameCode');
        $user = $request->input('user');

        try {
            // Buscar la partida con sus jugadores
            $game = Game::where('code', $gameCode)
                       ->with('players')
                       ->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ]);
            }

            // Encontrar el oponente (el jugador que no es el usuario actual)
            $opponent = $game->players->where('id', '!=', $user['id'])->first();

            // Actualizar la partida
            $game->update([
                'is_finished' => true,
                'end_date' => now(),
                'winner' => $opponent ? $opponent->id : null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Game finished successfully',
                'game' => $game
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error finishing the game'
            ]);
        }
    }

    /**
     * PLAY A PUBLIC GAME
     * This function searches a public game to join. If there are no public games available, it creates a new one.
     * Returns the game you have joined.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function playPublicGame($user)
    {
        
        // Obtener el usuario del frontend
        //$user = $request->input('user');

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
                    //'message' => 'Public free game found, connected successfully',
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
            //'message' => 'No games found, new one created and connected successfully',
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
                    'status'  => 'failed',
                    'message' => 'Game found, but is public.'
                ]);

            }else{

                // Obtener cantidad de jugadores
                $playersCount = $privateGame->players_count;

                if($playersCount > 1){

                    // Si hay más de 1 jugador la partida está llena
                    return response()->json([
                        'status'  => 'failed',
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
                'status'  => 'failed',
                'message' => 'Private game not found.'
            ]);
        }

    }

    /**
     * CHECK MATCH STATUS
     * Verifica el estado actual de la partida y devuelve todos sus datos
     */
    public function checkMatchStatus(Request $request) {
        $gameCode = $request->input('gameCode');
        
        // Buscar la partida con las mismas relaciones que otras funciones
        $game = Game::where('code', $gameCode)
                    ->with(['players', 'observers']) 
                    ->withCount('players')
                    ->first();
        
        if (!$game) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Partida no encontrada'
            ]);
        }

        // Devolver respuesta en el mismo formato que otras funciones
        return response()->json([
            'status' => 'success',
            'message' => $game->players_count >= 2 ? 'user joined' : 'waiting',
            'game' => $game
        ]);
    }

    /**
     * Obtiene el historial de las últimas 100 partidas del usuario
     */
    public function getUserMatchHistory(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $userController = new UserController();
            
            $games = Game::query()
                ->whereHas('players', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->orderByDesc('creation_date')
                ->take(100)
                ->get()
                ->map(function ($game) use ($userController) {
                    $winnerUsername = $game->winner ? 
                        $userController->getUsernameById($game->winner) : 
                        'Empate';

                    return [
                        'date' => $game->creation_date->format('d/m/Y'),  // Cambiado el formato
                        'is_public' => $game->is_public ? 'Pública' : 'Privada',
                        'winner_id' => $game->winner,
                        'winner' => $winnerUsername,
                        'id' => $game->id
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $games
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al obtener el historial de partidas'
            ], 500);
        }
    }

    /**
     * CREATE TIMESTAMP
     * Crea un timestamp para la partida después de 10 segundos
     */
    public function createTimestamp(Request $request) 
    {
        $gameCode = $request->input('gameCode');
        $updateColumn = $request->input('data');
        
        try {
            // Buscar la partida
            $game = Game::where('code', $gameCode)->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Partida no encontrada'
                ]);
            }

            // Habilitar log de consultas
            DB::enableQueryLog();

            // Actualizar el timestamp solicitado con now() + 15 segundos
            $game->update([
                $updateColumn => now()->addSeconds(5)
            ]);

            // Obtener el log de la consulta ejecutada
            $queries = DB::getQueryLog();
            Log::info('SQL Executed TIMESTAMP/LOG: ', $queries);

            return response()->json([
                'status' => 'success',
                'message' => 'Timestamp creado correctamente',
                'game' => $game
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al crear el timestamp'
            ]);
        }
    }

    /**
     * CHECK TIMESTAMP
     * Verifica si existe el timestamp solicitado
     */
    public function checkTimestamp(Request $request)
    {
        $gameCode = $request->input('gameCode');
        $checkColumn = $request->input('data');
        
        try {
            
            // Buscar la partida
            $game = Game::where('code', $gameCode)->first();
            
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Partida no encontrada'
                ]);
            }

            // Verificar si existe el timestamp
            if ($game->$checkColumn) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Timestamp encontrado',
                    'game' => $game
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Timestamp no encontrado',
                'game' => $game
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al verificar el timestamp'
            ]);
        }
    }

}