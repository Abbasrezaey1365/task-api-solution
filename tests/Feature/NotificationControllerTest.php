<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_one_seen(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // create a database notification
        $n = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TaskAssignedNotification',
            'data' => ['msg' => 'x'],
            'read_at' => null,
        ]);

        $this->postJson("/api/notifications/{$n->id}/mark-seen")
            ->assertStatus(200);

        $this->assertNotNull($user->fresh()->notifications()->first()->read_at);
    }

    public function test_unseen_returns_only_unread(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TaskAssignedNotification',
            'data' => ['msg' => 'unread'],
            'read_at' => null,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TaskAssignedNotification',
            'data' => ['msg' => 'read'],
            'read_at' => now(),
        ]);

        $res = $this->getJson("/api/notifications/unseen")
            ->assertStatus(200);

        // adapt this assert to your response shape:
        $this->assertCount(1, $res->json('data.data'));

    }
}
