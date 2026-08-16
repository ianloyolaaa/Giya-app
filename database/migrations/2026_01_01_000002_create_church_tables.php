<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD entities: ChurchCategories, Churches, ChurchImages, Schedules
 *
 * The flat `category` string on churches becomes category_id -> ChurchCategories,
 * and the single `image_url` column becomes the ChurchImages table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();       // Basilica, Cathedral, Shrine, Church, Chapel
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('church_categories')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->string('name', 200);
            $table->string('location', 200)->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index('category_id');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('church_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->string('image_url', 500);
            $table->string('caption', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['church_id', 'is_primary']);
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->string('event_name', 200);
            $table->string('event_type', 50)->default('Mass');   // Mass | Feast Day | Novena | Event
            $table->date('schedule_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence', 50)->nullable();        // Daily | Weekly | Monthly | Yearly
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['church_id', 'schedule_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('church_images');
        Schema::dropIfExists('churches');
        Schema::dropIfExists('church_categories');
    }
};
