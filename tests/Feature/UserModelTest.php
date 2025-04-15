<?php

namespace tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating and retrieving a user with specific attributes.
     *
     * @return void
     */
    public function test_can_create_and_retrieve_user_with_attributes(): void // Renamed for clarity
    {
        // 1. Arrange: Define specific data and create the user
        $userData = [
            'username' => 'testuser',
            'name' => 'Test',
            'surname1' => 'User',
            'surname2' => 'One',
            'nationality' => 'ES', // Example: Spain
            'email' => 'test.user.one@example.com',
            // Password will be hashed automatically if not provided and using factory defaults
        ];
        $user = User::factory()->create($userData);

        // 2. Act: Find the user in the database
        $foundUser = User::find($user->id);

        // 3. Assert: Verify the user was found and has the correct data
        $this->assertNotNull($foundUser);
        $this->assertEquals($userData['username'], $foundUser->username);
        $this->assertEquals($userData['name'], $foundUser->name);
        $this->assertEquals($userData['surname1'], $foundUser->surname1);
        $this->assertEquals($userData['surname2'], $foundUser->surname2);
        $this->assertEquals($userData['nationality'], $foundUser->nationality);
        $this->assertEquals($userData['email'], $foundUser->email);

        // Assert using assertDatabaseHas for direct DB check
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'username' => $userData['username'],
            'nationality' => $userData['nationality'],
        ]);

        // Optional: Assert a user without surname2 can be created
        $userWithoutSurname2 = User::factory()->create(['surname2' => null]);
        $this->assertDatabaseHas('users', [
            'id' => $userWithoutSurname2->id,
            'surname2' => null,
        ]);
        $this->assertNull($userWithoutSurname2->fresh()->surname2); // Check the model property too
    }

    /**
     * Test the default state of the factory.
     *
     * @return void
     */
    public function test_user_factory_default_state(): void
    {
        // Arrange: Create user with factory defaults
        $user = User::factory()->create();

        // Assert: Check that essential fields are not null/empty
        $this->assertNotNull($user->username);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->surname1);
        // surname2 can be null based on the factory definition
        $this->assertNotNull($user->nationality);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password); // Password should be hashed
        $this->assertNotNull($user->email_verified_at); // Default is verified

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test the unverified state of the factory.
     *
     * @return void
     */
    public function test_user_factory_unverified_state(): void
    {
        // Arrange: Create an unverified user
        $user = User::factory()->unverified()->create();

        // Assert: Check email_verified_at is null
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null,
        ]);
    }
}
