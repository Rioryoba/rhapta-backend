<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create tasks table if it doesn't exist (in case activities table was renamed from tasks)
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('status', ['not_started', 'in_progress', 'completed', 'on_hold'])->default('not_started');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
