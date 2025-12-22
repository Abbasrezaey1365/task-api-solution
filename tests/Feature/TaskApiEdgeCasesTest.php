<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_task_with_missing_fields()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [])
            ->assertStatus(422);
    }

    public function test_guest_cannot_access_tasks()
    {
        $this->getJson('/api/tasks')
            ->assertStatus(401);
    }
}
