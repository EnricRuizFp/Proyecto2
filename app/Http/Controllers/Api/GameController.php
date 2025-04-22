<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\GameViewer;
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
     * LISTAR PARTIDAS PAGINADO A 10
     * 
     * Devuelve un listado paginado de las partidas existentes.
     * 
     * Sin parámetros de entrada
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Listado de partidas en formato JSON.
     */
    public function index(Request $request)
    {
        // Ejemplo: Paginar partidas, cargando la relación del creador
        $games = Game::with('creator')->paginate(10); // Ajustar el tamaño de la paginación si es necesario

        // Devolver el objeto paginador directamente. Laravel maneja la estructura JSON.
        return $games;
    }

    /**
     * LISTAR PARTIDAS
     * 
     * Devuelve un listado de las partidas existentes.
     * 
     * Sin parámetros de entrada
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Listado de partidas en formato JSON.
     */
    public function getAllGames()
    {
        $games = Game::with('creator')->get();
        return response()->json($games);
    }

    /**
     * MOSTRAR PARTIDA
     * 
     * Devuelve los datos de una partida específica.
     * 
     * @param int $id
     * Datos esperados: ID de la partida.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Datos de la partida en formato JSON.
     */
    public function show($id)
    {
        $game = Game::findOrFail($id);
        return response()->json($game);
    }

    /**
     * CREAR PARTIDA SIMPLE
     * 
     * Crea una nueva partida con los datos proporcionados.
     * No necesita estar autenticado.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "is_public": boolean|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Datos de la partida creada en formato JSON.
     */
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'is_public' => 'required|boolean'
        ]);

        // Permitir que el usuario no esté autenticado
        $userId = auth()->check() ? auth()->id() : null;

        // Crear la partida
        $game = Game::create([
            'creation_date' => now(),
            'is_public'     => $request->is_public,
            'is_finished'   => false,
            'end_date'      => null,
            'created_by'    => $userId,
        ]);

        // Devolver la respuesta en formato JSON
        return response()->json([
            'message' => 'Game created successfully',
            'data'    => $game,
        ], 201);
    }

    /**
     * ACTUALIZAR PARTIDA
     *
     * Actualiza los datos de una partida existente.
     *
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request (ejemplos):
     * {
     *   "is_public": boolean|nullable
     *   "is_finished": boolean|nullable
     *   "winner": integer|nullable|exists:users,id
     *   "points": integer|nullable
     * }
     *
     * @param \App\Models\Game $game // Use Route Model Binding
     *
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Datos de la partida actualizada en formato JSON.
     */
    public function update(Request $request, Game $game) // Use Route Model Binding
    {
        // Validación de los datos recibidos (ajusta según los campos que permitas actualizar)
        $validatedData = $request->validate([
            'is_public' => 'sometimes|boolean', // 'sometimes' valida solo si está presente
            'is_finished' => 'sometimes|boolean',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'winner' => 'sometimes|nullable|integer|exists:users,id', // Asegura que el winner_id existe
            'points' => 'sometimes|nullable|integer',
            // NO incluyas 'created_by' o 'creation_date' aquí
        ]);

        // Actualizar solo los campos validados que están presentes en la request
        $game->fill($validatedData); // fill() asigna solo los atributos en $validatedData
        $game->save(); // Guarda los cambios

        // Devolver la respuesta en formato JSON
        return response()->json([
            // 'message' => 'Game updated successfully', // Opcional: añadir mensaje
            'data'    => $game->fresh(), // Devuelve el modelo actualizado desde la BD
        ]);
    }

    /**
     * ELIMINAR PARTIDA
     *
     * Elimina la partida especificada y todos sus datos relacionados
     * (jugadores, espectadores, movimientos).
     *
     * @param  \App\Models\Game  $game // Usando Route Model Binding
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(Game $game) // Cambiado a Route Model Binding
    {
        // Opcional: Añadir autorización si es necesario
        // Gate::authorize('delete', $game);

        try {
            // Iniciar una transacción de base de datos
            DB::transaction(function () use ($game) {

                // 1. Eliminar movimientos asociados (si existe la relación/modelo)
                // Asumiendo que el modelo Move tiene 'game_id'
                Move::where('game_id', $game->id)->delete();

                // 2. Eliminar espectadores asociados (usando el modelo GameViewer)
                GameViewer::where('game_id', $game->id)->delete();

                // 3. Eliminar jugadores asociados (usando el modelo GamePlayer)
                GamePlayer::where('game_id', $game->id)->delete();

                // 4. Eliminar chats asociados (si existe la relación/modelo)
                // Asumiendo que el modelo Chat tiene 'game_id'
                // Chat::where('game_id', $game->id)->delete(); // Descomentar si existe el modelo Chat

                // 5. Finalmente, eliminar el juego mismo
                $game->delete();

                Log::info("Game ID {$game->id} and associated data deleted successfully."); // Log opcional

            });

            // Devolver respuesta exitosa (204 No Content)
            return response()->noContent();
        } catch (\Exception $e) {
            // Registrar el error para depuración
            Log::error("Error deleting game ID {$game->id}: " . $e->getMessage());

            // Devolver una respuesta de error
            return response()->json([
                'message' => 'Error deleting game due to a server error.',
                // 'error' => $e->getMessage() // Opcionalmente incluir mensaje de error en desarrollo
            ], 500); // 500 Internal Server Error
        }
    }



    /*

        ///// GAME FUNCTIONS /////

    */

    /**
     * CHECK USER REQUIREMENTS
     * 
     * Combrueba si el usuario cumple con los requisitos para jugar una partida.
     * En caso de estar en una partida, la termina.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required,
     *     "username": string|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y mensaje en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function checkUserRequirements(Request $request)
    {

        // Validación de los datos recibidos
        $request->validate([
            'user' => 'required'
        ]);

        // Pasar valor a variable
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
     * 
     * Gestiona la búsqueda o creción de partidas según los parámetros de entrada.
     * Esta función tiene diversas salidas:
     * - Partida pública: Si no hay partidas públicas disponibles, crea una nueva y conecta al usuario.
     *                    Si hay partidas públicas disponibles, conecta al usuario a la primera que encuentre.
     * 
     * - Partida privada: Si no se pasa el código, crea una nueva partida privada y conecta al usuario.
     *                    Si se pasa el código, intenta unirse a la partida privada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameType": "public"|"private"|required,
     *   "user": {
     *     "id": int|required
     *   },
     *   "gameCode": string|nullable
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function findMatchFunction(Request $request)
    {
        // Validar los datos recibidos y settearlos en variables separadas
        // $request->validate([
        //     'gameType' => 'required|string|in:public,private',
        //     'user' => 'required|array',
        //     'user.id' => 'required|integer',
        //     'gameCode' => 'nullable|string'
        // ]);

        // Pasar datos a variables
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
        } else if ($gameType == "private") {

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
     * 
     * Finaliza la partida y asigna el ganador.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida finalizada en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function finishMatchFunction(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'gameCode' => 'required|string',
            'user' => 'required|array',
            'user.id' => 'required|integer'
        ]);

        // Pasar datos a variables
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
     * 
     * Busca una partida disponible o crea una nueva si no existen.
     * -> Si hay una partida pública disponible, se une a ella.
     * -> Si no hay partidas públicas, crea una nueva partida pública y se une a ella.
     * 
     * @param mixed $user
     * Datos esperados: Usuario que quiere jugar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function playPublicGame($user)
    {

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
            'joined' => now()
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
     * 
     * Crea una partida privada y conecta al usuario a ella.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida creada en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function createPrivateGame(Request $request)
    {

        // Get the authenticated user's ID
        $userId = $request->user['id'];

        // Crear juego privado
        $newPrivateGame = Game::create([
            'creation_date' => now(),
            'is_public'     => false,
            'is_finished'   => false,
            'end_date'      => null,
            'created_by'    => $userId // Use authenticated user ID
        ]);

        // Unirse a la partida privada
        $newPrivateGame->players()->attach($userId, [ // Use authenticated user ID
            'joined' => now()
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
     * 
     * Permite al usuario unirse a una partida privada mediante el código del juego.
     * -> Si la partida no existe, devuelve un mensaje de error.
     * -> Si la partida es pública, devuelve un mensaje de error.
     * -> Si la partida está llena, devuelve un mensaje de error.
     * 
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   },
     *   "code": string|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function joinPrivateGame(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'code' => 'required|string'
        ]);

        // Obtener el usuario autenticado y el código de la request
        $userId = $request->user['id'];
        $code = $request->input('code');

        // Buscar partida privada por código
        $privateGame = Game::where('code', $request->input('code'))
            ->where('is_public', false)
            ->first();

        // Verificar si la partida existe
        if (!$privateGame) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Private game not found.',
            ], 404); // Add the 404 status code
        }

        // Verificar si el usuario ya está en la partida
        if ($privateGame->players()->where('user_id', $userId)->exists()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'User already in this game.',
                'data'    => $privateGame
            ], 409);
        }

        // Verificar si la partida está llena (asumiendo 2 jugadores máximo)
        if ($privateGame->players()->count() >= 2) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Private game is full.',
            ], 400); // Add the 400 status code
        }

        // Unir al usuario a la partida
        $privateGame->players()->attach($userId, [
            'joined' => now()
        ]);

        // Marcar la partida como iniciada si ahora tiene 2 jugadores
        if ($privateGame->players()->count() == 2 && is_null($privateGame->start_date)) {
            $privateGame->update(['start_date' => now()]);
        }

        // Devolver la respuesta exitosa
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully joined the private game.',
            'data'    => $privateGame->fresh()
        ]);
    }

    /**
     * CHECK MATCH STATUS
     * 
     * Verifica el estado actual de la partida y devuelve sus datos.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function checkMatchStatus(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'gameCode' => 'required|string'
        ]);

        // Obtener el código de la partida del request
        $gameCode = $request->input('gameCode');

        // Buscar la partida por el código
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

        // Devolver respuesta (si no hay 2 usuarios, esperar a que los haya)
        return response()->json([
            'status' => 'success',
            'message' => $game->players_count >= 2 ? 'user joined' : 'waiting',
            'game' => $game
        ]);
    }

    /**
     * CREATE TIMESTAMP
     * 
     * Crea un timestamp en la columna especificada a 5 segundos vista.
     * -> Añade un timestamp en la columna que se especifique por parámetro "data"
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "data": string|required (Columna a actualizar)
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function createTimestamp(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'gameCode' => 'required|string',
            'data' => 'required|string'
        ]);

        // Obtener el código de la partida y la columna a actualizar
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

            // Actualizar el timestamp solicitado con now() + 5 segundos
            $game->update([
                $updateColumn => now()->addSeconds(5)
            ]);

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
     * 
     * Verifica si existe el timestamp solicitado.
     * -> Verifica si existe un timestamp en la columna especificada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "data": string|required (Columna a verificar)
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function checkTimestamp(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'gameCode' => 'required|string',
            'data' => 'required|string'
        ]);

        // Obtener el código de la partida y la columna a verificar
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
     * STORE SHIP PLACEMENT
     * 
     * Guarda la posición de los barcos del usuario en la partida.
     * Los datos se guardan en formato JSON y se especifica al barco al que pertenece cada coord.
     * -> Formato del JSON esperado:
     *    {"Acorazado": ["1,6", "1,7", "1,8", "1,9"], "Crucero_1": ["2,1", "2,2", "2,3"], "Crucero_2": ["2,4", "2,5", "2,6"], "Destructor": ["2,7", "2,8"], "Portaaviones": ["1,1", "1,2", "1,3", "1,4", "1,5"]}
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   },
     *   "shipsInfo": string|required (JSON con posiciones de los barcos)
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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

            // Subir las coordenadas a la partida
            $updated = $game->players()
                ->where('user_id', $request->user['id'])
                ->update(['coordinates' => $request->shipsInfo]);

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
            return response()->json([
                'status' => 'failed',
                'message' => 'Error storing ships placement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET OPPONENT SHIP PLACEMENT VALIDATION
     * 
     * Comprueba si el oponente ha colocado sus barcos en la partida.
     * -> Devuelve "OK" si el oponente ha colocado sus barcos.
     * -> Devuelve "NOK" si el oponente no ha colocado sus barcos.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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
            return response()->json([
                'status' => 'failed',
                'message' => 'Error checking opponent ships placement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET USER SHIP PLACEMENT
     * 
     * Devuelve las coordenadas de los barcos del usuario especificado en la partida especificada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y coordenadas de los barcos en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting user ship placement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET MATCH INFO
     * 
     * Devuelve toda la información posible de la partida especificada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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

            // Obtener todos los datos relacionados a la partida
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

            // Generar JSON con toda la información de la partida.
            $gameDetails = [
                'game' => $game,
                'players' => $players,
                'observers' => $observers,
                'meta' => [
                    'players_count' => $game->players_count,
                    'observers_count' => $game->observers_count,
                    'is_full' => $game->players_count >= 2,
                    'has_started' => !empty($game->start_date),
                    'has_finished' => !empty($game->end_date)
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $gameDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting match info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ATTACK POSITION
     * 
     * Procesa el ataque a una posición y verifica si le ha dado a un barco enemigo.
     * -> Formato de las coordenadas: 4,1 (x,y)
     * -> Devuelve "hit" si le ha dado a un barco, "miss" si no le ha dado a nada.
     * -> Devuelve "sunk" -> true si ha hundido un barco enemigo.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   },
     *   "coordinates": string|required (formato: "x,y")
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos del ataque en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function attackPosition(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'coordinates' => 'required|string'
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

            $hitShip = null;
            $isSunk = false;

            // Buscar si la coordenada coincide con algún barco
            foreach ($opponentShips as $shipName => $positions) {
                if (in_array($attackCoordinate, $positions)) {
                    $hitShip = $shipName;

                    // Verificar si todas las posiciones del barco han sido golpeadas
                    $allHits = true;
                    foreach ($positions as $position) {
                        // Si la posición actual no es la que acabamos de atacar
                        if ($position != $attackCoordinate) {
                            // Buscar si ya existe un hit en esa posición
                            $existingHit = Move::where('game_id', $game->id)
                                ->where('coordinate', $position)
                                ->where('result', 'hit')
                                ->exists();

                            if (!$existingHit) {
                                $allHits = false;
                                break;
                            }
                        }
                    }
                    $isSunk = $allHits;
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
                'sunk' => $isSunk,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $hitShip ? 'hit' : 'miss',
                'ship' => $hitShip,
                'is_sunk' => $isSunk
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error processing attack: ' . $e->getMessage(),
                'ship' => null
            ], 500);
        }
    }

    /**
     * GET LAST MOVE
     * 
     * Devuelve el último movimiento realizado por el usuario especificado en la partida especificada.
     * Se puede utilizar para obtener el último movimiento del propio jugador o del oponente.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos del último movimiento en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getLastMove(Request $request)
    {
        try {

            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer'
            ]);

            // Pasar los datos del request a variables
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

            // Obtener el último movimiento del usuario
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
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting last move'
            ], 500);
        }
    }

    /**
     * GET AVAILABLE GAME SHIPS
     * 
     * Devuelve los barcos disponibles para el juego.
     * -> Crea 2 cruceros iguales para mayor similitud con el juego original.
     * 
     * Sin parámetros de entrada.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Datos de los barcos en formato JSON.
     * Respuesta error: No devuelve nada.
     */
    public function getAvailableGameShips()
    {
        try {
            // Obtener todos los barcos de la base de datos
            $ships = DB::table('ships')->get();

            // Crear el array de barcos para el juego
            $gameShips = [];

            // Generar el array de barcos con nombre y tamaño
            foreach ($ships as $ship) {
                if ($ship->name === 'Crucero') {
                    // Agregar los dos cruceros con nombres diferentes
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
            return response()->json([], 500);
        }
    }

    /**
     * GET USER MOVES
     * 
     * Devuelve todos los movimientos del usuario especificado en la partida especificada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de los movimientos en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getUserMoves(Request $request)
    {
        try {

            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer'
            ]);

            // Pasar los datos del request a variables
            $gameCode = $request->input('gameCode');
            $user = $request->input('user');

            // Buscar la partida (a partir del código)
            $game = Game::where('code', $gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener el player_id del game_player de la partida
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
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving moves'
            ], 500);
        }
    }

    /**
     * GET OPPONENT SHIP PLACEMENT GAME
     * 
     * Devuelve el estado actual de la patida, incluyendo datos calculados de la partida.
     * -> Cantidad de de barcos restantes, si alguien ha ganado y quién ha ganado.
     * -> Cantidad de movimientos realizados en la partida.
     * -> Como adición, el campo "data" devuelve las coordenadas de los barcos del oponente.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida ampliados en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
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
     * 
     * Gestiona el fin de una partida.
     * -> Actualiza los ránkings y puntuaciones de los usuarios involucrados.
     * -> Posibles estados: "draw", "winner", "loser".
     * -> El valor de los puntos a ganar o perder se calcula respecto a los rankings de los usuarios.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   },
     *   "status": "winner"|"loser"|"draw"|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida finalizada en formato JSON.
     *                    -> Devuelve los puntos ganados / perdidos y quién ganó. 
     * Respuesta error: Mensaje de error en formato JSON.
     * 
     */
    public function setGameEnding(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'status' => 'required|string|in:winner,loser,draw'
            ]);

            // Pasar los datos del request a variables
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

            // Si es empate, actualizar el estado del juego
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

                // Obtener los rankings actuales -> llamar directamente al modelo Ranking
                $winnerRanking = Ranking::where('user_id', $winner->user_id)->first();
                $loserRanking = Ranking::where('user_id', $loser->user_id)->first();

                // Calcular los puntos a modificar basado en los puntos actuales
                $points = ($winnerRanking->points < $loserRanking->points) ? 6 : 3;

                // Actualizar el ranking del ganador
                DB::table('rankings')
                    ->where('user_id', $winner->user_id)
                    ->increment('points', $points);

                // Verificar que los puntos a restar no dejen al perdedor con puntos negativos
                $pointsToDecrease = min($points, $loserRanking->points);

                // Actualizar el ranking del perdedor
                DB::table('rankings')
                    ->where('user_id', $loser->user_id)
                    ->decrement('points', $pointsToDecrease);

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

            // Si el jugador que hace la petición es el perdedor
            if ($status === 'loser') {

                // Identificar al perdedor como el usuario que envía la petición
                $loser = $players->where('user_id', $userId)->first();
                $winner = $players->where('user_id', '!=', $userId)->first();

                // Obtener los rankings actuales
                $winnerRanking = Ranking::where('user_id', $winner->user_id)->first();
                $loserRanking = Ranking::where('user_id', $loser->user_id)->first();

                // Calcular los puntos a modificar
                $points = ($winnerRanking->points < $loserRanking->points) ? 6 : 3;

                // Actualizar el ranking del ganador
                DB::table('rankings')
                    ->where('user_id', $winner->user_id)
                    ->increment('points', $points);

                // Verificar que los puntos a restar no dejen al perdedor con puntos negativos
                $pointsToDecrease = min($points, $loserRanking->points);

                // Actualizar el ranking del perdedor
                DB::table('rankings')
                    ->where('user_id', $loser->user_id)
                    ->decrement('points', $pointsToDecrease);

                // Actualizar el estado del juego
                $game->update([
                    'is_finished' => true,
                    'end_date' => now(),
                    'winner' => $winner->user_id,
                    'points' => $points
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Game ended with loser',
                    'winner_id' => $winner->user_id,
                    'points_earned' => $points,
                    'points_lost_by_loser' => $pointsToDecrease
                ]);
            }

            return response()->json(['status' => 'failed', 'message' => 'Invalid status']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Error processing game ending']);
        }
    }





    /*

        ///// MY PROFILE FUNCTIONS /////

    */

    /**
     * GET MATCH HISTORY
     * 
     * Devuelve las últimas 100 partidas del usuario.
     * -> Incluye su información y el ganador.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos del historial de partidas en formato JSON.
     *                    -> Incluye el ganador, la fecha y información de la partida.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getUserMatchHistory(Request $request)
    {
        try {
            // Pasar los datos del request a variables
            $userId = $request->user()->id;
            $userController = new UserController();

            // Obtener el historial de partidas del usuario
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



    /*

        ///// VIEW GAME FUNCTIONS /////

    */

    /**
     * GET AVAILABLE GAMES
     * 
     * Devuelve todas las partidas disponibles para observar.
     * -> Partidas disponibles son: aquellas con 2 jugadores y empezadas.
     * 
     * Sin parámetros de entrada.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de las partidas disponibles en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getAvailableGames()
    {
        try {
            // Obtener las partidas que tienen 2 jugadores
            $twoPlayerGames = DB::table('game_players')
                ->select('game_id')
                ->groupBy('game_id')
                ->havingRaw('COUNT(*) = 2')
                ->pluck('game_id');

            // Luego obtenemos los detalles de esas partidas (sólo las públicas y empezadas [start_date hace más de 10s])
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
                ->whereRaw('DATE_ADD(g.start_date, INTERVAL 10 SECOND) <= ?', [now()]) // Cambiado para verificar start_date + 15 segundos
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

            return response()->json($games);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    /**
     * GET CURRENT MATCH STATUS
     * 
     * Obtiene la información de una partida que esta en curso.
     * Sólo se debe usar en caso de ser el especator de una partida (GAME VIEWER)
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos de la partida en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function getCurrentMatchStatus(Request $request)
    {
        try {
            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string'
            ]);

            // Buscar la partida por código
            $game = Game::where('code', $request->gameCode)
                ->with(['observers'])
                ->withCount(['players', 'observers'])
                ->first();

            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener los jugadores con sus datos y coordenadas
            $players = DB::table('game_players')
                ->where('game_id', $game->id)
                ->join('users', 'users.id', '=', 'game_players.user_id')
                ->select('users.*', 'game_players.coordinates')
                ->get();

            // Agregar los jugadores al objeto del juego
            $game->players = $players;

            return response()->json([
                'status' => 'success',
                'data' => $game
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting game status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * VIEW GAME
     * 
     * Une al jugador pasado por parámetro a la partida especificada como espectador.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *    }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function viewGame(Request $request)
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
                    'message' => 'Game not found'
                ], 404);
            }

            // Verificar si el usuario ya es observador
            $existingViewer = DB::table('game_viewers')
                ->where('game_id', $game->id)
                ->where('user_id', $request->user['id'])
                ->first();

            if ($existingViewer) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User is already viewing this game'
                ]);
            }

            // Añadir el usuario como observador
            DB::table('game_viewers')->insert([
                'game_id' => $game->id,
                'user_id' => $request->user['id'],
                'joined' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully joined as viewer'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error joining as viewer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * VIEW GAME MOVES
     * 
     * Develve todos los movimientos y el estado actual de la partida para los espectadores.
     * Incluye la siguiente información:
     * -> Información sobre la partida:
     *    -> Datos de la partida.
     *    -> Cantidad restante de movimientos permitidos.
     *    -> El ganador actual en el momento (player_id o empate)
     *    -> Cantidad de hits del ganador.
     * -> Información sobre los jugadores:
     *    -> Sus datos.
     *    -> Las coordenadas de sus barcos
     *    -> Los movimientos de ha realizado (con fechas, resultado y más detalles)
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "gameCode": string|required,
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Datos detallados de la partida en el momento actual en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function viewGameMoves(Request $request)
    {
        try {

            // Validar datos requeridos
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array'
            ]);

            // Obtener el juego especificado
            $game = Game::where('code', $request->gameCode)->first();

            if (!$game) {
                Log::warning('Game not found: ' . $request->gameCode);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener los jugadores y sus datos
            $players = DB::table('game_players as gp')
                ->join('users as u', 'u.id', '=', 'gp.user_id')
                ->where('gp.game_id', $game->id)
                ->select('u.id', 'u.username', 'gp.coordinates')
                ->get();

            // Para cada jugador, obtener sus movimientos, estado de los barcos y toda su información relacionada
            foreach ($players as $player) {

                // Obtener todos los movimientos ordenados por fecha
                $moves = Move::where('game_id', $game->id)
                    ->whereHas('gamePlayer', function ($q) use ($player) {
                        $q->where('user_id', $player->id);
                    })
                    ->select('coordinate', 'result', 'ship', 'sunk', 'updated_at')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                // Mapear los movimientos con manejo seguro de fecha
                $player->moves = $moves->map(function ($move) {
                    $timestamp = null;

                    // Convertir el timestamp de la fecha de la DB a datos usables en este formato: Y-m-d H:i:s
                    try {
                        // Para ello vamos a usar Carbon.
                        // -> ¿Qué es carbon? Biblioteca útil para manejar fechas en caso de no ser de tipo fecha
                        if (is_string($move->updated_at)) {
                            $timestamp = \Carbon\Carbon::parse($move->updated_at)->format('Y-m-d H:i:s');
                        } else {
                            $timestamp = $move->updated_at ? $move->updated_at->format('Y-m-d H:i:s') : null;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error formatting timestamp: ' . $e->getMessage());
                    }

                    // Devolver los datos del movimiento
                    return [
                        'coordinate' => $move->coordinate,
                        'result' => $move->result,
                        'ship' => $move->ship,
                        'is_sunk' => $move->sunk,
                        'timestamp' => $timestamp
                    ];
                });

                // Contar hits y calcular barcos restantes
                $player->hits = Move::where('game_id', $game->id)
                    ->whereHas('gamePlayer', function ($q) use ($player) {
                        $q->where('user_id', $player->id);
                    })
                    ->where('result', 'hit')
                    ->count();

                // Calcular barcos restantes
                if ($player->coordinates) {
                    try {
                        // Decodificar las coordenadas de los barcos de JSON a Obj
                        $ships = json_decode($player->coordinates, true);

                        // Obtener los baros que se han undido (cantidad)
                        $sunkShips = Move::where('game_id', $game->id)
                            ->where('result', 'hit')
                            ->where('sunk', true)
                            ->whereHas('gamePlayer', function ($q) use ($player) {
                                $q->where('user_id', '!=', $player->id);
                            })
                            ->count();

                        // Calcular barcos restantes con el total y hundidos
                        $player->remaining_ships = count($ships) - $sunkShips;
                    } catch (\Exception $e) {
                        $player->remaining_ships = 0;
                    }
                } else {
                    $player->remaining_ships = 0;
                }
            }

            // Obtener la cantida de movimientos realizada en la partida y los movimientos restantes
            $totalMoves = Move::where('game_id', $game->id)->count();
            $remainingMoves = 200 - $totalMoves;

            // Generar respuesta final
            $response = [
                'status' => 'success',
                'data' => [
                    'players' => $players->map(function ($player) {
                        return [
                            'id' => $player->id,
                            'username' => $player->username,
                            'coordinates' => $player->coordinates,
                            'moves' => $player->moves,
                            'remaining_ships' => $player->remaining_ships,
                            'hits' => $player->hits
                        ];
                    }),
                    'game_status' => [
                        'remaining_moves' => $remainingMoves,
                        'current_leader' => $players[0]->hits > $players[1]->hits ?
                            $players[0]->username : ($players[1]->hits > $players[0]->hits ? $players[1]->username : "Empate"),
                        'leader_hits' => max($players[0]->hits, $players[1]->hits),
                        'is_finished' => $game->is_finished,
                        'winner' => $game->winner ?
                            $players->firstWhere('id', $game->winner)->username : ($game->is_finished && !$game->winner ? 'draw' : null)
                    ]
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error getting game moves: ' . $e->getMessage()
            ], 500);
        }
    }
}
