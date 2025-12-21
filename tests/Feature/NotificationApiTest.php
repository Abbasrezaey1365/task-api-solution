<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unseen_notifications_and_mark_all_seen(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();

        // Owner creates project
        $project = Project::query()->create([
            'user_id' => $owner->id,
            'name' => 'P1',
            'description' => null,
        ]);

        // Owner creates assigned task -> should trigger notification for assignee
        Sanctum::actingAs($owner);

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'assigned_user_id' => $assignee->id,
            'title' => 'Assigned task',
            'description' => null,
            'status' => 'todo',
            'due_date' => now()->addDays(2)->toDateString(),
        ])->assertStatus(201);

        // Assignee checks unseen
        Sanctum::actingAs($assignee);

        $unseen = $this->getJson('/api/notifications/unseen');
        $unseen->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        // Mark all as seen
        $this->postJson('/api/notifications/mark-all-seen')
            ->assertStatus(200)
            ->assertJsonPath('data.success', true);

        // After marking, unseen should be empty
        $unseenAfter = $this->getJson('/api/notifications/unseen');
        $unseenAfter->assertStatus(200);

        $this->assertSame(0, (int) ($unseenAfter->json('data.total') ?? 0));
    }
}
