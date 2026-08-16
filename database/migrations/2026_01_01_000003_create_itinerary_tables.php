<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD entities: ItineraryType, Itineraries, ItineraryStops
 *
 * The flat `type` string on itineraries becomes itinerary_type_id, and the
 * `church_name` snapshot on stops is dropped in favour of the church_id join.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();      // Custom, Visita Iglesia, Heritage Tour
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('devotees')->cascadeOnDelete();
            $table->foreignId('itinerary_type_id')
                  ->constrained('itinerary_types')
                  ->restrictOnDelete();
            $table->string('name', 200);
            $table->string('status', 50)->default('Draft');   // Draft | Active | Completed | Cancelled
            $table->date('schedule_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['user_id', 'status']);
        });

        Schema::create('itinerary_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('itineraries')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->integer('stop_order');
            $table->boolean('is_visited')->default(false);
            $table->timestamp('visited_at')->nullable();
            $table->text('notes')->nullable();

            $table->index(['itinerary_id', 'stop_order']);
            $table->unique(['itinerary_id', 'church_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_stops');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('itinerary_types');
    }
};
