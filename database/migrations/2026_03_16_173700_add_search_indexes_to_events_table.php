<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['status', 'event_date'], 'events_status_event_date_idx');
            $table->index(['category_id', 'event_date'], 'events_category_event_date_idx');
            $table->index(['event_type', 'event_date'], 'events_type_event_date_idx');
            $table->index(['city'], 'events_city_idx');
            $table->index(['base_price'], 'events_base_price_idx');
            $table->index(['is_featured', 'event_date'], 'events_featured_event_date_idx');
            $table->index(['latitude', 'longitude'], 'events_geo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_event_date_idx');
            $table->dropIndex('events_category_event_date_idx');
            $table->dropIndex('events_type_event_date_idx');
            $table->dropIndex('events_city_idx');
            $table->dropIndex('events_base_price_idx');
            $table->dropIndex('events_featured_event_date_idx');
            $table->dropIndex('events_geo_idx');
        });
    }
};
