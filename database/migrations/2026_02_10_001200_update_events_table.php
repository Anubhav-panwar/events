<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('description');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->json('audience')->nullable()->after('tags');
            $table->boolean('is_featured')->default(false)->index()->after('status');
            $table->string('event_type')->default('free')->index()->after('is_featured'); // free/paid
            $table->decimal('base_price', 10, 2)->nullable()->after('event_type');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['venue_name', 'city', 'country', 'audience', 'is_featured', 'event_type', 'base_price']);
        });
    }
};
