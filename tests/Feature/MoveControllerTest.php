<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Move;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MoveControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test atacar posición exitosamente y no dar a ningun barco
     */
    public function test_attack_position_successfully_hits(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();
        $game = Game::factory()->create();

        // Crear game_player
        $gamePlayer = GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'joined' => now()
        ]);

        // Crear el oponente con coordenadas de barcos
        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $opponent->id,
            'joined' => now(),
            'coordinates' => json_encode([
                'Destructor' => ['4,1', '4,2']
            ])
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/attack', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id],
                'coordinates' => '4,1'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'hit',
                'ship' => 'Destructor',
                'is_sunk' => false
            ]);
    }

    /**
     * Test atacar posición y fallar el tiro
     */
    public function test_attack_position_successfully_misses(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();
        $game = Game::factory()->create();

        // Crear jugadores
        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'joined' => now()
        ]);

        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $opponent->id,
            'joined' => now(),
            'coordinates' => json_encode([
                'Destructor' => ['4,1', '4,2']
            ])
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/attack', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id],
                'coordinates' => '1,1' // Coordenada donde no hay nada
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'miss',
                'ship' => null,
                'is_sunk' => false
            ]);
    }

    /**
     * Test atacar posición con código de juego inválido
     */
    public function test_attack_position_fails_with_invalid_game_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/attack', [
                'gameCode' => 'INVALID',
                'user' => ['id' => $user->id],
                'coordinates' => '1,1'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Game not found'
            ]);
    }

    /**
     * Test obtener último movimiento exitosamente
     */
    public function test_get_last_move_successfully(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();
        $game = Game::factory()->create();

        // Crear game_player
        $gamePlayer = GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'joined' => now()
        ]);

        // Crear movimiento
        Move::create([
            'game_id' => $game->id,
            'game_player_id' => $gamePlayer->id,
            'coordinate' => '1,1',
            'result' => 'miss',
            'updated_at' => now()
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/get-last-move', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id]
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'move',
                'timestamp'
            ]);
    }

    /**
     * Test obtener último movimiento con código de juego inválido
     */
    public function test_get_last_move_fails_with_invalid_game_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/get-last-move', [
                'gameCode' => 'INVALID',
                'user' => ['id' => $user->id]
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Game not found'
            ]);
    }

    /**
     * Test obtener movimientos del usuario exitosamente
     */
    public function test_get_user_moves_successfully(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        // Crear game_player
        $gamePlayer = GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'joined' => now()
        ]);

        // Crear varios movimientos
        Move::create([
            'game_id' => $game->id,
            'game_player_id' => $gamePlayer->id,
            'coordinate' => '1,1',
            'result' => 'miss'
        ]);
        Move::create([
            'game_id' => $game->id,
            'game_player_id' => $gamePlayer->id,
            'coordinate' => '1,2',
            'result' => 'hit'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/get-user-moves', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonStructure([
                'status',
                'moves' => [
                    '*' => [
                        'id',
                        'game_id',
                        'game_player_id',
                        'coordinate',
                        'result'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'moves');
    }

    /**
     * Test obtener movimientos del usuario con código de juego inválido
     */
    public function test_get_user_moves_fails_with_invalid_game_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/get-user-moves', [
                'gameCode' => 'INVALID',
                'user' => ['id' => $user->id]
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Game not found'
            ]);
    }

    /**
     * Test obtener movimientos con usuario no perteneciente al juego
     */
    public function test_get_user_moves_fails_with_invalid_user(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/games/get-user-moves', [
                'gameCode' => $game->code,
                'user' => ['id' => $user->id]
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Player not found in game'
            ]);
    }
}
