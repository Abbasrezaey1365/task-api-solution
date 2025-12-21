<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Abbas',
            'email' => 'abbas@example.com',
            'password' => 'password1234',
        ])->assertStatus(201);

        $hit429At = null;

        for ($i = 1; $i <= 50; $i++) {
            $res = $this->postJson('/api/login', [
                'email' => 'abbas@example.com',
                'password' => 'wrong-password',
            ]);

            if ($res->getStatusCode() === 429) {
                $hit429At = $i;
                break;
            }
        }

        $this->assertNotNull($hit429At, 'Expected login to be rate-limited but never hit 429 within 50 requests.');
    }
}
