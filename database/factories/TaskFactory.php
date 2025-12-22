<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $ownerId = auth()->id();

        if ($ownerId) {
            $project = Project::factory()->create([
                'user_id' => $ownerId,
            ]);

            return [
                'project_id' => $project->id,
                'user_id' => $ownerId,
                'assignee_id' => null,
                'assigned_user_id' => null,
                'title' => $this->faker->sentence(3),
                'description' => $this->faker->optional()->paragraph(),
                'status' => 'todo',
                'due_date' => null,
            ];
        }

        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        return [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'assignee_id' => null,
            'assigned_user_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'status' => 'todo',
            'due_date' => null,
        ];
    }
}
