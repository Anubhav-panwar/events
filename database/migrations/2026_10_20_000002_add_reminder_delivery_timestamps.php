<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('event_reminders', 'queued_at')) {
                $table->timestamp('queued_at')->nullable();
            }
            if (!Schema::hasColumn('event_reminders', 'attempted_at')) {
                $table->timestamp('attempted_at')->nullable();
            }
            if (!Schema::hasColumn('event_reminders', 'provider_accepted_at')) {
                $table->timestamp('provider_accepted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_reminders', function (Blueprint $table) {
            $columns = array_filter(['queued_at', 'attempted_at', 'provider_accepted_at'], fn ($column) => Schema::hasColumn('event_reminders', $column));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
