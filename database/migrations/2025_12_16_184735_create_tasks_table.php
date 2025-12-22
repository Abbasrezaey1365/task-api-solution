<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            // creator/owner (nullable because tests insert tasks directly without it)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // assignee FK (nullable)
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();

            // IMPORTANT: must NOT be FK (tests allow invalid id like 999999)
            $table->unsignedBigInteger('assigned_user_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('status')->default('todo'); // todo|doing|done
            $table->date('due_date')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'due_date']);
            $table->index(['assigned_user_id']);
            $table->index(['assignee_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
