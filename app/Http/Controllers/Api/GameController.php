<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayer;
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

    public function checkUserRequirements(Request $request){

        // Inclusión de los datos pasados por parámetro
        $gameType = $request->input('gameType');
        $gameCode = $request->input('gameCode');
        $user = $request->input('user');

        /*
            ///// COMRPOBACIONES PRE PARTIDA /////
        */

        // Usuario registrado
        if(!$user){
            return response()->json([
                'status'  => 'failed',
                'message' => 'You are not logged in.',
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
                'message' => 'Your user is currently in a game. Unfinished match has been finished. Wait some seconds.',
                'game' => null
            ]);
        }

        // Response OK
        return response()->json([
            'status' => 'success',
            'message' => 'User ready to play a game'
        ]);

        // Jugar partida
        if($gameType == "public"){

            $response = GameController::playPublicGame($user);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Joined to a game',
                'game' =>  $response->getData()->data
            ]);

        }else if($gameType == "private"){

            if(!$gameCode){

                $response = GameController::createPrivateGame(new Request(['user' => $user]));

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Creando partida privada.',
                    'game' => $response->getData()->data
                ]);
            }

            $response = GameController::joinPrivateGame(new Request(['user' => $user, 'code' => $gameCode]));

            if($response->getData()->status == 'failed'){

                return response()->json([
                    'status' => 'failed',
                    'message' => $response->getData()->message,
                    'game' => null
                ]);

            }

            return response()->json(data: [
                'status'  => 'success',
                'message' => 'Entrando a partida privada.',
                'game' => $response->getData()
            ]);
            
        }

        // No game type selected
        return response()->json([
            'status'  => 'failed',
            'message' => 'No game type selected'
        ]);

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
     * Obtiene el historial de las últimas 100 partidas del usuario
     */
    public function getUserMatchHistory(Request $request)
    {
        try {
            $userId = $request->user()->id;
            
            // Obtener las últimas 100 partidas donde el usuario participó
            $games = Game::whereHas('players', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['players.user'])
                ->orderBy('created_at', 'desc')
                ->take(100)
                ->get()
                ->map(function ($game) use ($userId) {
                    // Encontrar al oponente (el otro jugador que no es el usuario actual)
                    $opponent = $game->players
                        ->where('user_id', '!=', $userId)
                        ->first();

                    // Determinar el resultado
                    $result = 'draw';
                    if ($game->winner_id === $userId) {
                        $result = 'victory';
                    } elseif ($game->winner_id && $game->winner_id !== $userId) {
                        $result = 'defeat';
                    }

                    return [
                        'opponent' => $opponent ? $opponent->user->username : 'Desconocido',
                        'date' => $game->created_at->format('d/m/Y H:i'),
                        'result' => $result
                    ];
                });

            $message = null;
            $count = count($games);
            
            if ($count === 0) {
                $message = "¡Tu barco está tan nuevo que aún tiene el plástico protector!";
            } elseif ($count < 10) {
                $message = "¡El mar es grande y tú apenas estás mojando los pies!";
            }

            return response()->json([
                'status' => 'success',
                'data' => $games,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error al obtener el historial de partidas'
            ], 500);
        }
    }

}