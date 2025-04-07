<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Move; // Añadir este import al inicio del archivo
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RankingController; // Añadir este import al inicio del archivo
use App\Models\User;
use App\Models\Ranking; // Añadir este import al inicio del archivo
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
    public function checkUserRequirements(Request $request)
    {

        // Inclusión de los datos pasados por parámetro
        $gameType = $request->input('gameType');
        $gameCode = $request->input('gameCode');
        $user = $request->input('user');

        /*
            ///// COMPROBACIONES PRE PARTIDA /////
        */

        // Usuario registrado - Validación más estricta
        if (!$user || !isset($user['id']) || !$user['username']) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'You must be logged in to play.',
                'game' => null
            ]);
        }

        // Usuario en otra partida
        $unfinishedUserGames = Game::whereIn('id', function ($query) use ($user) {
            $query->select('game_id')->from('game_players')->where('user_id', $user);
        })->where('is_finished', false)->get();
        if (count($unfinishedUserGames) > 0) {

            // Terminar todas las posibles partidas que tenga el usuario
            foreach ($unfinishedUserGames as $game) {

                // Obtener los jugadores de la partida
                $players = GamePlayer::where('game_id', $game->id)->get();

                // Si hay dos jugadores, asignar el ganador, si hay 1, dejar el ganador como null
                if ($players->count() == 2) {
                    // El ganador es el otro jugador
                    $winner = $players->first()->user_id == $user ? $players->last()->user_id : $players->first()->user_id;
                } else {
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

    /**
     * FINISH MATCH FUNCTION
     * Esta función finaliza la partida y asigna el ganador.
     * @param \Illuminate\Http\Request $request
     */
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
            // Verificar si la partida ya está terminada
            if ($game->is_finished) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Game was already finished',
                    'game' => $game
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
        if ($privateGame) {

            // Obtener el tipo de partida
            if ($privateGame->is_public) {

                // Devolver mensaje partida pública
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Game found, but is public.'
                ]);
            } else {

                // Obtener cantidad de jugadores
                $playersCount = $privateGame->players_count;

                if ($playersCount > 1) {

                    // Si hay más de 1 jugador la partida está llena
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Private game is full.'
                    ]);
                } else {

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
        } else {

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
    public function checkMatchStatus(Request $request)
    {
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
                ->whereHas('players', function ($query) use ($userId) {
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

    /**
     * GET AVAILABLE GAMES
     * Esta función obtiene las partidas disponibles para observar.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAvailableGames()
    {
        try {
            // Primero obtenemos las partidas que tienen exactamente 2 jugadores
            $twoPlayerGames = DB::table('game_players')
                ->select('game_id')
                ->groupBy('game_id')
                ->havingRaw('COUNT(*) = 2')
                ->pluck('game_id');

            // Luego obtenemos los detalles de esas partidas
            $games = DB::table('games as g')
                ->select([
                    'g.id',
                    'g.code',
                    DB::raw('MIN(u.username) as player1'),
                    DB::raw('MAX(u.username) as player2')
                ])
                ->join('game_players as gp', 'g.id', '=', 'gp.game_id')
                ->join('users as u', 'gp.user_id', '=', 'u.id')
                ->whereIn('g.id', $twoPlayerGames)
                ->where('g.is_finished', false)
                ->where('g.is_public', true)
                ->whereNotNull('g.start_date')
                ->whereNull('g.end_date')
                ->groupBy('g.id', 'g.code')
                ->get()
                ->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'code' => $game->code,
                        'player1' => $game->player1,
                        'player2' => $game->player2
                    ];
                });

            Log::info('Partidas disponibles encontradas: ' . $games->count());
            return response()->json($games);
        } catch (\Exception $e) {
            Log::info('No hay partidas disponibles para observar');
            return response()->json([], 200);
        }
    }

    /**
     * STORE SHIP PLACEMENT
     * Esta función almacena las posiciones de los barcos del usuario en la partida pasada por parámetro.
     * @param \Illuminate\Http\Request $request
     */
    public function storeShipPlacement(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'shipsInfo' => 'required|string'
            ]);

            // Buscar la partida por código
            $game = Game::where('code', $request->gameCode)
                ->with('players')
                ->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Verificar que el usuario es parte de la partida
            $gamePlayer = $game->players()
                ->where('user_id', $request->user['id'])
                ->first();

            if (!$gamePlayer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User is not part of this game'
                ], 403);
            }

            // Actualizar la colocación de barcos
            DB::enableQueryLog(); // Para debugging

            $updated = $game->players()
                ->where('user_id', $request->user['id'])
                ->update(['coordinates' => $request->shipsInfo]);

            Log::info('SQL Query Ships Placement: ', DB::getQueryLog());

            if (!$updated) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Could not update ships placement'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ships placement stored successfully',
                'data' => [
                    'game_code' => $game->code,
                    'user_id' => $request->user['id']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing ships placement: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error storing ships placement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET OPPONENT SHIP PLACEMENT VALIDATION
     * Esta función verifica si el oponente ha colocado sus barcos en la partida.
     * @param \Illuminate\Http\Request $request
     */
    public function getOpponentShipPlacementValidation(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer'
            ]);

            // Buscar la partida por código
            $game = Game::where('code', $request->gameCode)->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener el registro del oponente en game_players
            $opponentPlayer = DB::table('game_players')
                ->where('game_id', $game->id)
                ->where('user_id', '!=', $request->user['id'])
                ->first();

            if (!$opponentPlayer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Opponent not found'
                ], 404);
            }

            // Verificar si el campo coordinates tiene datos
            return response()->json([
                'status' => 'success',
                'message' => !empty($opponentPlayer->coordinates) ? 'OK' : 'NOK'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking opponent ships placement: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error checking opponent ships placement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET USER SHIP PLACEMENT
     * Esta función obtiene la colocación de barcos del usuario en la partida.
     * @param \Illuminate\Http\Request $request
     */
    public function getUserShipPlacement(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer'
            ]);

            // Buscar la partida por código
            $game = Game::where('code', $request->gameCode)->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener el registro del jugador en game_players
            $playerData = DB::table('game_players')
                ->where('game_id', $game->id)
                ->where('user_id', $request->user['id'])
                ->first();

            if (!$playerData) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Player not found'
                ], 404);
            }

            // Verificar si existen coordenadas
            if (empty($playerData->coordinates)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No se han encontrado coordenadas'
                ]);
            }

            // Devolver las coordenadas encontradas
            return response()->json([
                'status' => 'success',
                'data' => $playerData->coordinates
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user ship placement: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting user ship placement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET MATCH INFO
     * Esta función obtiene toda la información de la partida actual.
     * @param \Illuminate\Http\Request $request
     */
    public function getMatchInfo(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string'
            ]);

            // Buscar la partida por código con todas sus relaciones
            $game = Game::where('code', $request->gameCode)
                ->with(['players', 'observers'])
                ->withCount(['players', 'observers'])
                ->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener todos los datos relacionados usando DB para tener más detalle
            $players = DB::table('game_players')
                ->where('game_id', $game->id)
                ->join('users', 'users.id', '=', 'game_players.user_id')
                ->select('game_players.*', 'users.username', 'users.email')
                ->get();

            $observers = DB::table('game_viewers')
                ->where('game_id', $game->id)
                ->join('users', 'users.id', '=', 'game_viewers.user_id')
                ->select('game_viewers.*', 'users.username')
                ->get();

            // Construir respuesta detallada
            $gameDetails = [
                'game' => $game,
                'players' => $players,
                'observers' => $observers,
                'meta' => [
                    'players_count' => $game->players_count,
                    'observers_count' => $game->observers_count,
                    'is_full' => $game->players_count >= 2,
                    'has_started' => !empty($game->start_date),
                    'has_finished' => !empty($game->end_date),
                    'duration' => null // Simplemente dejamos duration como null
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $gameDetails
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting match info: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting match info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ATTACK POSITION
     * Esta función verifica si un ataque ha dado a un barco enemigo
     * @param \Illuminate\Http\Request $request
     */
    public function attackPosition(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'coordinates' => 'required|string'  // Formato: "row,col"
            ]);

            // Buscar la partida
            $game = Game::where('code', $request->gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener las coordenadas del oponente
            $opponentData = DB::table('game_players')
                ->where('game_id', $game->id)
                ->where('user_id', '!=', $request->user['id'])
                ->first();
            if (!$opponentData || empty($opponentData->coordinates)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Opponent ships not found'
                ], 404);
            }

            // Convertir las coordenadas del oponente a array
            $opponentShips = json_decode($opponentData->coordinates, true);
            $attackCoordinate = $request->coordinates;

            // Buscar si la coordenada coincide con algún barco
            $hitShip = null;
            foreach ($opponentShips as $shipName => $positions) {
                if (in_array($attackCoordinate, $positions)) {
                    $hitShip = $shipName;
                    break;
                }
            }

            // Obtener el game_player_id del jugador actual
            $gamePlayer = GamePlayer::where('game_id', $game->id)
                ->where('user_id', $request->user['id'])
                ->first();

            // Guardar el movimiento en la tabla moves
            Move::create([
                'game_id' => $game->id,
                'game_player_id' => $gamePlayer->id,
                'coordinate' => $attackCoordinate,
                'result' => $hitShip ? 'hit' : 'miss',
                'ship' => $hitShip,
                'updated_at' => now() // Añadir la fecha actual
            ]);

            // Si no ha encontrado coincidencia, es un fallo
            return response()->json([
                'status' => 'success',
                'message' => $hitShip ? 'hit' : 'miss',
                'ship' => $hitShip
            ]);

        } catch (\Exception $e) {
            Log::error('Error in attack position: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error processing attack: ' . $e->getMessage(),
                'ship' => null
            ], 500);
        }
    }

    /**
     * GET LAST MOVE
     * Esta función obtiene el último movimiento realizado por el usuario en la partida.
     * @param \Illuminate\Http\Request $request
     */
    public function getLastMove(Request $request)
    {
        try {
            $gameCode = $request->input('gameCode');
            $user = $request->input('user');

            // Buscar la partida
            $game = Game::where('code', $gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener el último movimiento del usuario actual
            $lastUserMove = Move::where('game_id', $game->id)
                ->whereHas('gamePlayer', function ($query) use ($user) {
                    $query->where('user_id', $user['id']);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            // Si hay un movimiento del usuario, buscar el movimiento del oponente posterior a este
            if ($lastUserMove) {
                $lastOpponentMove = Move::where('game_id', $game->id)
                    ->whereHas('gamePlayer', function ($query) use ($user) {
                        $query->where('user_id', '!=', $user['id']);
                    })
                    ->where('updated_at', '>', $lastUserMove->updated_at) // Movimiento posterior al del usuario
                    ->orderBy('updated_at', 'asc') // Ordenar por el más reciente después del usuario
                    ->first();
            } else {
                // Si no hay movimientos del usuario, obtener el primer movimiento del oponente
                $lastOpponentMove = Move::where('game_id', $game->id)
                    ->whereHas('gamePlayer', function ($query) use ($user) {
                        $query->where('user_id', '!=', $user['id']);
                    })
                    ->orderBy('updated_at', 'asc')
                    ->first();
            }

            return response()->json([
                'status' => 'success',
                'move' => $lastOpponentMove,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting last move: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting last move'
            ], 500);
        }
    }

    /**
     * GET AVAILABLE GAME SHIPS
     * Esta función obtiene todos los barcos disponibles para el juego.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAvailableGameShips()
    {
        try {
            // Obtener todos los barcos de la base de datos
            $ships = DB::table('ships')->get();

            // Crear el array de barcos para el juego
            $gameShips = [];

            foreach ($ships as $ship) {
                if ($ship->name === 'Crucero') {
                    // Agregar los dos destructores con nombres diferentes
                    $gameShips[] = [
                        'name' => 'Crucero_1',
                        'size' => $ship->size
                    ];
                    $gameShips[] = [
                        'name' => 'Crucero_2',
                        'size' => $ship->size
                    ];
                } else {
                    // Agregar el resto de barcos normalmente
                    $gameShips[] = [
                        'name' => $ship->name,
                        'size' => $ship->size
                    ];
                }
            }

            return response()->json($gameShips);
        } catch (\Exception $e) {
            Log::error('Error getting available ships: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * GET USER MOVES
     * Esta función obtiene todos los movimientos realizados por el usuario en una partida
     */
    public function getUserMoves(Request $request)
    {
        try {
            $gameCode = $request->input('gameCode');
            $user = $request->input('user');

            // Buscar la partida
            $game = Game::where('code', $gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener el ID del game_player del usuario
            $gamePlayer = GamePlayer::where('game_id', $game->id)
                ->where('user_id', $user['id'])
                ->first();

            if (!$gamePlayer) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Player not found in game'
                ], 404);
            }

            // Obtener todos los movimientos del usuario
            $moves = Move::where('game_id', $game->id)
                ->where('game_player_id', $gamePlayer->id)
                ->get();

            return response()->json([
                'status' => 'success',
                'moves' => $moves
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user moves: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving moves'
            ], 500);
        }
    }

    /**
     * GET OPPONENT SHIP PLACEMENT GAME
     * Esta función obtiene el estado actual de la partida, incluyendo los barcos restantes
     * y si el usuario ha ganado.
     * @param \Illuminate\Http\Request $request
     */
    public function getOpponentShipPlacementGame(Request $request)
    {
        try {

            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer'
            ]);

            // Buscar la partida
            $game = Game::where('code', $request->gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found',
                    'ships_left' => 0,
                    'has_winned' => false,
                    'move_count' => 0
                ], 404);
            }

            // Obtener datos del oponente
            $opponentData = DB::table('game_players')
                ->where('game_id', $game->id)
                ->where('user_id', '!=', $request->user['id'])
                ->first();

            if (!$opponentData || empty($opponentData->coordinates)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Opponent data not found',
                    'ships_left' => 0,
                    'has_winned' => false,
                    'move_count' => 0
                ], 404);
            }

            // Obtener movimientos del usuario
            $gamePlayer = GamePlayer::where('game_id', $game->id)
                ->where('user_id', $request->user['id'])
                ->first();

            // Contar el total de movimientos (hits + misses)
            $moveCount = Move::where('game_id', $game->id)
                ->where('game_player_id', $gamePlayer->id)
                ->count();

            $userMoves = Move::where('game_id', $game->id)
                ->where('game_player_id', $gamePlayer->id)
                ->where('result', 'hit')
                ->pluck('coordinate')
                ->toArray();

            // Procesar barcos del oponente
            $opponentShips = json_decode($opponentData->coordinates, true);
            $shipsLeft = 0;
            $totalShips = count($opponentShips);

            // Verificar cada barco
            foreach ($opponentShips as $shipName => $positions) {
                $hits = 0;
                foreach ($positions as $position) {
                    if (in_array($position, $userMoves)) {
                        $hits++;
                    }
                }
                
                // Si no todos los puntos del barco han sido golpeados, aumentar contador
                if ($hits < count($positions)) {
                    $shipsLeft++;
                }
            }

            $hasWinned = $shipsLeft === 0;

            return response()->json([
                'status' => 'success',
                'message' => $shipsLeft > 0 ? "Quedan {$shipsLeft} barcos por hundir" : "¡Todos los barcos han sido hundidos!",
                'ships_left' => $shipsLeft,
                'has_winned' => $hasWinned,
                'move_count' => $moveCount,
                'data' => $opponentData->coordinates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error checking game state: ' . $e->getMessage(),
                'ships_left' => 0,
                'has_winned' => false,
                'move_count' => 0
            ], 500);
        }
    }

    /**
     * SET GAME ENDING
     * Esta función establece el final de la partida y actualiza el estado de los jugadores.
     * @param \Illuminate\Http\Request $request
     */
    public function setGameEnding(Request $request)
    {
        try {
            $gameCode = $request->input('gameCode');
            $userId = $request->input('user')['id'];
            $status = $request->input('status');

            // Buscar el juego
            $game = Game::where('code', $gameCode)->first();
            if (!$game) {
                return response()->json(['status' => 'failed', 'message' => 'Game not found']);
            }

            // Obtener los jugadores
            $players = GamePlayer::where('game_id', $game->id)
                ->with('user')
                ->get();

            // Si es empate, simplemente actualizar el estado del juego
            if ($status === 'draw') {
                $game->update([
                    'is_finished' => true,
                    'end_date' => now(),
                    'winner' => null
                ]);
                return response()->json(['status' => 'success', 'message' => 'Game ended in draw']);
            }

            // Si es victoria, procesar los rankings y puntos
            if ($status === 'winner') {
                // Encontrar al ganador y perdedor
                $winner = $players->where('user_id', $userId)->first();
                $loser = $players->where('user_id', '!=', $userId)->first();

                // Obtener los rankings actuales usando Ranking model directamente
                $winnerRanking = Ranking::where('user_id', $winner->user_id)->first();
                $loserRanking = Ranking::where('user_id', $loser->user_id)->first();

                // Calcular los puntos a modificar basado en los puntos actuales
                $points = ($winnerRanking->points < $loserRanking->points) ? 6 : 3;

                // Actualizar los rankings
                DB::table('rankings')
                    ->where('user_id', $winner->user_id)
                    ->increment('points', $points);

                DB::table('rankings')
                    ->where('user_id', $loser->user_id)
                    ->decrement('points', $points);

                // Actualizar el estado del juego
                $game->update([
                    'is_finished' => true,
                    'end_date' => now(),
                    'winner' => $userId,
                    'points' => $points
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Game ended with winner',
                    'points_earned' => $points
                ]);
            }

            return response()->json(['status' => 'failed', 'message' => 'Invalid status']);
        } catch (\Exception $e) {
            Log::error('Error in setGameEnding: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Error processing game ending']);
        }
    }
}