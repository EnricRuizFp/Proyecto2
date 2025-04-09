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
     * Obtener todos los mensajes de un juego específico
     */
    public function getMessages(Request $request)
    {
        try {
            $request->validate([
                'gameCode' => 'required|string'
            ]);

            $game = Game::where('code', $request->gameCode)->first();
            if (!$game) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Game not found'
                ], 404);
            }

            // Obtener los mensajes con información del usuario
            $messages = DB::table('chats')
                ->join('users', 'users.id', '=', 'chats.user_id')
                ->where('chats.game_id', $game->id)
                ->select('chats.*', 'users.username')
                ->orderBy('chats.id', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting chat messages: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error retrieving chat messages'
            ], 500);
        }
    }

    /**
     * Guardar un nuevo mensaje en el chat
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'gameCode' => 'required|string',
                'user' => 'required|array',
                'user.id' => 'required|integer',
                'message' => 'required|string|max:255'
            ]);

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

            return response()->json([
                'status' => 'success',
                'data' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending chat message: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Error sending message'
            ], 500);
        }
    }
}
