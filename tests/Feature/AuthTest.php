<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $res = $this->postJson('/api/register', [
            'name' => 'Abbas',
            'email' => 'abbas@example.com',
            'password' => 'password1234',
        ]);

        $res->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'abbas@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Abbas',
            'email' => 'abbas@example.com',
            'password' => 'password1234',
        ])->assertStatus(201);

        $login = $this->postJson('/api/login', [
            'email' => 'abbas@example.com',
            'password' => 'password1234',
        ]);

        $login->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_login_with_wrong_password_returns_422(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Abbas',
            'email' => 'abbas@example.com',
            'password' => 'password1234',
        ])->assertStatus(201);

        $login = $this->postJson('/api/login', [
            'email' => 'abbas@example.com',
            'password' => 'wrong-password',
        ]);

        $login->assertStatus(422);
    }
}
