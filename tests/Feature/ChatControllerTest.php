<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test obtener mensajes de un juego que no existe
     */
    public function test_get_messages_returns_404_for_nonexistent_game(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/games/chat/get-messages', [
                'gameCode' => '1234' // Código de juego (no existente en ningún caso)
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Game not found'
            ]);
    }

    /**
     * Test enviar mensaje a un juego que no existe
     */
    public function test_send_message_fails_with_nonexistent_game(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/games/chat/send-message', [
                'gameCode' => '1234', // Código de juego (no existe en ningún caso)
                'user' => ['id' => $user->id],
                'message' => 'Test message'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Game not found'
            ]);
    }

    /**
     * Test enviar mensaje con datos inválidos
     */
    public function test_send_message_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/games/chat/send-message', [
                'gameCode' => $game->code,
                'user' => [], // Usuario inválido
                'message' => 'Test message'
            ]);

        $response->assertStatus(500);
    }

    /**
     * Test enviar mensaje exitosamente
     */
    public function test_send_message_stores_message_successfully(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/games/chat/send-message', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id],
                'message' => 'Hola, esto es un mensaje de prueba.'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'game_id',
                    'user_id',
                    'message',
                    'date'
                ]
            ]);

        $this->assertDatabaseHas('chats', [
            'game_id' => $game->id,
            'user_id' => $user->id,
            'message' => 'Hola, esto es un mensaje de prueba.'
        ]);
    }

    /**
     * Test obtener mensajes de un chat existente
     */
    public function test_get_messages_returns_messages_successfully(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        
        // Crear mensajes de prueba
        Chat::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'message' => 'Test message 1.'
        ]);
        Chat::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'message' => 'Test message 2.'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/games/chat/get-messages', [
                'gameCode' => $game->code
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'game_id',
                        'user_id',
                        'message',
                        'date'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');
    }
}
