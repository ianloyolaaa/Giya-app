<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD entities: SubscriptionPlans, Transactions
 *
 * The flat `plan` string on transactions becomes plan_type_id, matching the
 * ERD's "is used in" relationship from SubscriptionPlans to Transactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 10)->default('PHP');
            $table->integer('duration_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('devotees')->cascadeOnDelete();
            $table->foreignId('plan_type_id')
                  ->constrained('subscription_plans')
                  ->restrictOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('PHP');
            $table->string('method', 50)->nullable();          // GCash | Maya | Card | Cash
            $table->string('status', 50)->default('Pending');  // Pending | Paid | Failed | Refunded
            $table->string('reference_no', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['user_id', 'status']);
            $table->index('reference_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('subscription_plans');
    }
};
