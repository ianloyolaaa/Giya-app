<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD entities: VisitHistory, Feedback, Favorites
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('devotees')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->foreignId('itinerary_id')->nullable()
                  ->constrained('itineraries')->nullOnDelete();
            $table->timestamp('visited_at')->useCurrent();
            $table->string('completion_status', 50)->default('Completed'); // Completed | Partial | Skipped
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['user_id', 'visited_at']);
            $table->index('church_id');
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('devotees')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->foreignId('visit_history_id')->nullable()
                  ->constrained('visit_history')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();   // 1-5
            $table->text('comment')->nullable();
            $table->string('status', 50)->default('Pending');    // Pending | Approved | Rejected
            $table->timestamp('created_at')->useCurrent();

            $table->index(['church_id', 'status']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('devotees')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            // ERD: UNIQUE(user_id, church_id)
            $table->unique(['user_id', 'church_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('visit_history');
    }
};
