<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ranking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test obtener puntos del usuario exitosamente
     */
    public function test_get_user_points_successfully(): void
    {
        $user = User::factory()->create();
        $ranking = Ranking::create([
            'user_id' => $user->id,
            'points' => 100,
            'wins' => 5,
            'losses' => 3,
            'draws' => 1
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/user-points');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'points' => 100
            ]);
    }

    /**
     * Test obtener puntos de usuario sin ranking
     */
    public function test_get_user_points_returns_zero_for_new_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/user-points');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'points' => 0
            ]);
    }

    /**
     * Test obtener posición global exitosamente
     */
    public function test_get_global_position_successfully(): void
    {
        $user = User::factory()->create();

        // Crear rankings de usuarios con más puntos
        Ranking::create(['user_id' => User::factory()->create()->id, 'points' => 150]);
        Ranking::create(['user_id' => User::factory()->create()->id, 'points' => 120]);

        // Crear el ranking del usuario de prueba
        Ranking::create(['user_id' => $user->id, 'points' => 100]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/global-position');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'position' => 3
            ]);
    }

    /**
     * Test obtener posición global sin ranking
     */
    public function test_get_global_position_returns_null_for_new_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/global-position');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'position' => null
            ]);
    }

    /**
     * Test obtener posición nacional exitosamente
     */
    public function test_get_national_position_successfully(): void
    {
        // Crear 3 usuarios de la misma nacionalidad
        $user = User::factory()->create(['nationality' => 'Africa']);
        $otherUser1 = User::factory()->create(['nationality' => 'Africa']);
        $otherUser2 = User::factory()->create(['nationality' => 'Africa']);
        
        // Crear rankings para los usuarios
        Ranking::create(['user_id' => $otherUser1->id, 'points' => 150]);
        Ranking::create(['user_id' => $otherUser2->id, 'points' => 120]);
        Ranking::create(['user_id' => $user->id, 'points' => 100]);

        // Crear usuario de otra nacionalidad (para probar que la posiciónn nacional no se ve afectada)
        $foreignUser = User::factory()->create(['nationality' => 'America']);
        Ranking::create(['user_id' => $foreignUser->id, 'points' => 200]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/national-position');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'position' => 3 // El usuario de otra nacionalidad no debería aparecer en el ranking nacional
            ]);
    }

    /**
     * Test obtener posición nacional sin nacionalidad
     */
    public function test_get_national_position_fails_without_nationality(): void
    {
        $user = User::factory()->create(['nationality' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/national-position');

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'failed',
                'message' => 'User nationality not set'
            ]);
    }

    /**
     * Test obtener ranking nacional exitosamente
     */
    public function test_get_national_ranking_successfully(): void
    {
        // Crear 3 usuarios de la misma nacionalidad
        $user = User::factory()->create(['nationality' => 'Europa']);
        $spanishUser1 = User::factory()->create(['nationality' => 'Europa']);
        $spanishUser2 = User::factory()->create(['nationality' => 'Europa']);
        
        // Crear rankings para los usuarios
        Ranking::create(['user_id' => $spanishUser1->id, 'points' => 150]);
        Ranking::create(['user_id' => $spanishUser2->id, 'points' => 120]);
        Ranking::create(['user_id' => $user->id, 'points' => 100]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/national');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user_nationality' => 'Europa'
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'user_id',
                        'points',
                        'user' => [
                            'username',
                            'nationality'
                        ]
                    ]
                ],
                'user_nationality'
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test obtener ranking nacional sin nacionalidad
     */
    public function test_get_national_ranking_fails_without_nationality(): void
    {
        $user = User::factory()->create(['nationality' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/rankings/national');

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'failed',
                'message' => 'User nationality not set to view national rankings.'
            ]);
    }
}
