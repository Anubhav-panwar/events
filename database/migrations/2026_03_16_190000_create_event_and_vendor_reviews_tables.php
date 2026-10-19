<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_reviews')) {
            Schema::create('event_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('review')->nullable();
                $table->boolean('is_verified_attendee')->default(true);
                $table->timestamps();

                $table->unique(['event_id', 'user_id']);
                $table->index(['event_id', 'rating']);
            });
        }

        if (!Schema::hasTable('vendor_reviews')) {
            Schema::create('vendor_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('review')->nullable();
                $table->boolean('is_verified_customer')->default(true);
                $table->timestamps();

                $table->unique(['vendor_profile_id', 'user_id']);
                $table->index(['vendor_profile_id', 'rating']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
        Schema::dropIfExists('event_reviews');
    }
};
