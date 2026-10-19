<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('event_date')->nullable()->change();
            $table->time('start_time')->nullable()->change();
            $table->string('event_type')->nullable()->change();
            if (!Schema::hasColumn('events', 'dates_announced_at')) {
                $table->timestamp('dates_announced_at')->nullable()->index()->after('is_date_confirmed');
            }
        });

        Schema::table('event_reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('event_reminders', 'notification_status')) {
                $table->string('notification_status')->default('pending')->index()->after('status');
            }
            if (!Schema::hasColumn('event_reminders', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('last_sent_at');
            }
            if (!Schema::hasColumn('event_reminders', 'notification_error')) {
                $table->text('notification_error')->nullable()->after('notified_at');
            }
            if (!Schema::hasColumn('event_reminders', 'retry_count')) {
                $table->unsignedTinyInteger('retry_count')->default(0)->after('notification_error');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('event_reminders')) {
            $colsToDrop = [];
            foreach (['notification_status', 'notified_at', 'notification_error', 'retry_count'] as $col) {
                if (Schema::hasColumn('event_reminders', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                Schema::table('event_reminders', function (Blueprint $table) use ($colsToDrop) {
                    $table->dropColumn($colsToDrop);
                });
            }
        }

        if (Schema::hasTable('events') && Schema::hasColumn('events', 'dates_announced_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('dates_announced_at');
            });
        }
    }
};
