<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $isMySql = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb']);

        if ($isMySql) {
            foreach (['events', 'users', 'orders', 'vendor_profiles', 'event_reviews', 'vendor_reviews'] as $t) {
                if (Schema::hasTable($t)) {
                    try {
                        DB::statement("ALTER TABLE `{$t}` ENGINE = InnoDB");
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!Schema::hasTable('event_reviews')) {
            Schema::create('event_reviews', function (Blueprint $table) {
                $table->engine = 'InnoDB';
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
        } else {
            Schema::table('event_reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('event_reviews', 'event_id')) {
                    $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('event_reviews', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('event_reviews', 'order_id')) {
                    $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                }
                if (!Schema::hasColumn('event_reviews', 'rating')) {
                    $table->unsignedTinyInteger('rating');
                }
                if (!Schema::hasColumn('event_reviews', 'review')) {
                    $table->text('review')->nullable();
                }
                if (!Schema::hasColumn('event_reviews', 'is_verified_attendee')) {
                    $table->boolean('is_verified_attendee')->default(true);
                }
            });

            $existingFks = collect(Schema::getForeignKeys('event_reviews'))->pluck('columns')->flatten()->all();
            Schema::table('event_reviews', function (Blueprint $table) use ($existingFks) {
                if (!in_array('event_id', $existingFks, true) && Schema::hasColumn('event_reviews', 'event_id')) {
                    $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
                }
                if (!in_array('user_id', $existingFks, true) && Schema::hasColumn('event_reviews', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('order_id', $existingFks, true) && Schema::hasColumn('event_reviews', 'order_id')) {
                    $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                }
            });
        }

        if (!Schema::hasTable('vendor_reviews')) {
            Schema::create('vendor_reviews', function (Blueprint $table) {
                $table->engine = 'InnoDB';
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
        } else {
            Schema::table('vendor_reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_reviews', 'vendor_profile_id')) {
                    $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('vendor_reviews', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('vendor_reviews', 'order_id')) {
                    $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                }
                if (!Schema::hasColumn('vendor_reviews', 'rating')) {
                    $table->unsignedTinyInteger('rating');
                }
                if (!Schema::hasColumn('vendor_reviews', 'review')) {
                    $table->text('review')->nullable();
                }
                if (!Schema::hasColumn('vendor_reviews', 'is_verified_customer')) {
                    $table->boolean('is_verified_customer')->default(true);
                }
            });

            $existingFks = collect(Schema::getForeignKeys('vendor_reviews'))->pluck('columns')->flatten()->all();
            Schema::table('vendor_reviews', function (Blueprint $table) use ($existingFks) {
                if (!in_array('vendor_profile_id', $existingFks, true) && Schema::hasColumn('vendor_reviews', 'vendor_profile_id')) {
                    $table->foreign('vendor_profile_id')->references('id')->on('vendor_profiles')->cascadeOnDelete();
                }
                if (!in_array('user_id', $existingFks, true) && Schema::hasColumn('vendor_reviews', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('order_id', $existingFks, true) && Schema::hasColumn('vendor_reviews', 'order_id')) {
                    $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_reviews');
        Schema::dropIfExists('event_reviews');
    }
};
