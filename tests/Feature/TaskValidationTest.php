<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TaskValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_task_with_missing_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [])
            ->assertStatus(422);
    }
}
