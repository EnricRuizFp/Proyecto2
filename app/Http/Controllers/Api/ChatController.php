<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * OBTENER MENSAJES
     * 
     * Devuelve todos los mensajes asociados a un juego en específico.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "gameCode": string|required (Código único del juego)
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Listado de mensajes en formato JSON.
     * Respuesta error: error y mensaje de error en formato JSON.
     */
    public function getMessages(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'gameCode' => 'required|string'
            ]);

            // Obtener los datos del juego
            $game = Game::where('code', $request->gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener los mensajes con información del usuario a partir del gameID
            $messages = DB::table('chats')
                ->join('users', 'users.id', '=', 'chats.user_id')
                ->where('chats.game_id', $game->id)
                ->select('chats.*', 'users.username')
                ->orderBy('chats.id', 'asc')
                ->get();

            // Devolver la respuesta en formato JSON
            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {

            // Crear LOG y devolver error
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving chat messages'
            ], 500);
        }
    }

    /**
     * ENVIAR MENSAJE
     * 
     * Guarda un nuevo mensaje en el chat de la partida.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "gameCode": string|required,
     *    "user": { --> El usuario actual
     *      "id": integer|required
     *    },
     *    "message": string|required|max:255
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje guardado en formato JSON.
     * Respuesta error: error y mensaje de error en formato JSON.
     */
    public function sendMessage(Request $request)
    {
        try {

            // Validar los datos de entrada
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'message' => 'required|string|max:255'
            ]);

            // Obtener los datos del juego
            $game = Game::where('code', $request->gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Crear el nuevo mensaje
            $chat = new Chat();
            $chat->game_id = $game->id;
            $chat->user_id = $request->user['id'];
            $chat->message = $request->message;
            $chat->save();

            // Devolver el mensaje con información del usuario
            $message = DB::table('chats')
                ->join('users', 'users.id', '=', 'chats.user_id')
                ->where('chats.id', $chat->id)
                ->select('chats.*', 'users.username')
                ->first();

            // Devolver la respuesta en formato JSON
            return response()->json([
                'status' => 'success',
                'data' => $message
            ]);
        } catch (\Exception $e) {

            // Crear LOG y devolver error
            return response()->json([
                'status' => 'failed',
                'message' => 'Error sending message'
            ], 500);
        }
    }
}
