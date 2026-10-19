<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMySql = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb']);

        // 1. Ensure InnoDB storage engine for all tables in the database on MySQL/MariaDB
        if ($isMySql) {
            $dbName = Schema::getConnection()->getDatabaseName();
            try {
                $allTables = DB::select("
                    SELECT TABLE_NAME, ENGINE
                    FROM information_schema.TABLES
                    WHERE (TABLE_SCHEMA = DATABASE() OR LOWER(TABLE_SCHEMA) = LOWER(?))
                      AND TABLE_TYPE = 'BASE TABLE'
                ", [$dbName]);

                foreach ($allTables as $t) {
                    if ($t->ENGINE && strcasecmp($t->ENGINE, 'InnoDB') !== 0) {
                        DB::statement("ALTER TABLE `{$t->TABLE_NAME}` ENGINE = InnoDB");
                    }
                }
            } catch (\Throwable $e) {}

            // Explicitly ensure all core referenced parent and child tables are converted to InnoDB
            $coreTables = [
                'users', 'vendor_profiles', 'categories', 'category_vendor_profile',
                'events', 'event_media', 'ticket_types', 'orders', 'order_items', 'tickets',
                'event_saves', 'vendor_follows', 'posts', 'event_share_clicks', 'vendor_media',
                'event_referrals', 'event_reminders',
                'event_reviews', 'vendor_reviews', 'vendor_share_clicks', 'user_block_records'
            ];
            foreach ($coreTables as $tableName) {
                if (Schema::hasTable($tableName)) {
                    try {
                        DB::statement("ALTER TABLE `{$tableName}` ENGINE = InnoDB");
                    } catch (\Throwable $e) {}
                }
            }
        }

        // 2. Audit for orphaned records before adding foreign keys
        $orphanChecks = [
            [
                'table' => 'event_reviews',
                'column' => 'event_id',
                'ref_table' => 'events',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'event_reviews',
                'column' => 'user_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'event_reviews',
                'column' => 'order_id',
                'ref_table' => 'orders',
                'ref_col' => 'id',
                'nullable' => true,
            ],
            [
                'table' => 'vendor_reviews',
                'column' => 'vendor_profile_id',
                'ref_table' => 'vendor_profiles',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'vendor_reviews',
                'column' => 'user_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'vendor_reviews',
                'column' => 'order_id',
                'ref_table' => 'orders',
                'ref_col' => 'id',
                'nullable' => true,
            ],
            [
                'table' => 'vendor_share_clicks',
                'column' => 'vendor_profile_id',
                'ref_table' => 'vendor_profiles',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'vendor_share_clicks',
                'column' => 'user_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => true,
            ],
            [
                'table' => 'user_block_records',
                'column' => 'user_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => false,
            ],
            [
                'table' => 'user_block_records',
                'column' => 'admin_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => true,
            ],
            [
                'table' => 'users',
                'column' => 'blocked_by_id',
                'ref_table' => 'users',
                'ref_col' => 'id',
                'nullable' => true,
            ],
        ];

        foreach ($orphanChecks as $check) {
            if (!Schema::hasTable($check['table']) || !Schema::hasTable($check['ref_table'])) {
                continue;
            }
            if (!Schema::hasColumn($check['table'], $check['column'])) {
                continue;
            }

            $query = DB::table($check['table'])
                ->whereNotIn($check['column'], function ($sub) use ($check) {
                    $sub->select($check['ref_col'])->from($check['ref_table']);
                });

            if ($check['nullable']) {
                $query->whereNotNull($check['column']);
            }

            $orphans = $query->pluck($check['column'])->all();
            if (!empty($orphans)) {
                $count = count($orphans);
                throw new \RuntimeException(
                    "Orphaned records detected! Table `{$check['table']}` has {$count} record(s) where `{$check['column']}` values do not exist in `{$check['ref_table']}`.`{$check['ref_col']}`. Offending values: " . implode(', ', array_slice($orphans, 0, 10))
                );
            }
        }

        // 3. Ensure users table has blocking columns, index, and foreign key
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_blocked')) {
                    $table->boolean('is_blocked')->default(false)->index()->after('role');
                }
                if (!Schema::hasColumn('users', 'blocked_at')) {
                    $table->timestamp('blocked_at')->nullable()->after('is_blocked');
                }
                if (!Schema::hasColumn('users', 'blocked_reason')) {
                    $table->text('blocked_reason')->nullable()->after('blocked_at');
                }
                if (!Schema::hasColumn('users', 'blocked_by_id')) {
                    $table->foreignId('blocked_by_id')->nullable()->after('blocked_reason')->constrained('users')->nullOnDelete();
                }
            });

            if (Schema::hasColumn('users', 'blocked_by_id')) {
                $usersFks = collect(Schema::getForeignKeys('users'));
                $hasBlockedByFk = $usersFks->contains(function ($fk) {
                    return in_array('blocked_by_id', $fk['columns'] ?? [], true);
                });
                if (!$hasBlockedByFk) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->foreign('blocked_by_id')->references('id')->on('users')->nullOnDelete();
                    });
                }
            }
        }

        // 4. Ensure event_reviews foreign keys exist
        if (Schema::hasTable('event_reviews')) {
            $eventReviewsFks = collect(Schema::getForeignKeys('event_reviews'));
            $fkColumns = $eventReviewsFks->pluck('columns')->flatten()->all();

            Schema::table('event_reviews', function (Blueprint $table) use ($fkColumns) {
                if (!in_array('event_id', $fkColumns, true) && Schema::hasColumn('event_reviews', 'event_id')) {
                    $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
                }
                if (!in_array('user_id', $fkColumns, true) && Schema::hasColumn('event_reviews', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('order_id', $fkColumns, true) && Schema::hasColumn('event_reviews', 'order_id')) {
                    $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                }
            });
        }

        // 5. Ensure vendor_reviews foreign keys exist
        if (Schema::hasTable('vendor_reviews')) {
            $vendorReviewsFks = collect(Schema::getForeignKeys('vendor_reviews'));
            $fkColumns = $vendorReviewsFks->pluck('columns')->flatten()->all();

            Schema::table('vendor_reviews', function (Blueprint $table) use ($fkColumns) {
                if (!in_array('vendor_profile_id', $fkColumns, true) && Schema::hasColumn('vendor_reviews', 'vendor_profile_id')) {
                    $table->foreign('vendor_profile_id')->references('id')->on('vendor_profiles')->cascadeOnDelete();
                }
                if (!in_array('user_id', $fkColumns, true) && Schema::hasColumn('vendor_reviews', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('order_id', $fkColumns, true) && Schema::hasColumn('vendor_reviews', 'order_id')) {
                    $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
                }
            });
        }

        // 6. Ensure vendor_share_clicks foreign keys exist
        if (Schema::hasTable('vendor_share_clicks')) {
            $vendorShareClicksFks = collect(Schema::getForeignKeys('vendor_share_clicks'));
            $fkColumns = $vendorShareClicksFks->pluck('columns')->flatten()->all();

            Schema::table('vendor_share_clicks', function (Blueprint $table) use ($fkColumns) {
                if (!in_array('vendor_profile_id', $fkColumns, true) && Schema::hasColumn('vendor_share_clicks', 'vendor_profile_id')) {
                    $table->foreign('vendor_profile_id')->references('id')->on('vendor_profiles')->cascadeOnDelete();
                }
                if (!in_array('user_id', $fkColumns, true) && Schema::hasColumn('vendor_share_clicks', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        // 7. Ensure user_block_records foreign keys exist
        if (Schema::hasTable('user_block_records')) {
            $userBlockRecordsFks = collect(Schema::getForeignKeys('user_block_records'));
            $fkColumns = $userBlockRecordsFks->pluck('columns')->flatten()->all();

            Schema::table('user_block_records', function (Blueprint $table) use ($fkColumns) {
                if (!in_array('user_id', $fkColumns, true) && Schema::hasColumn('user_block_records', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('admin_id', $fkColumns, true) && Schema::hasColumn('user_block_records', 'admin_id')) {
                    $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_block_records')) {
            $fks = collect(Schema::getForeignKeys('user_block_records'))->pluck('columns')->flatten()->all();
            Schema::table('user_block_records', function (Blueprint $table) use ($fks) {
                if (in_array('admin_id', $fks, true)) {
                    $table->dropForeign(['admin_id']);
                }
                if (in_array('user_id', $fks, true)) {
                    $table->dropForeign(['user_id']);
                }
            });
        }

        if (Schema::hasTable('vendor_share_clicks')) {
            $fks = collect(Schema::getForeignKeys('vendor_share_clicks'))->pluck('columns')->flatten()->all();
            Schema::table('vendor_share_clicks', function (Blueprint $table) use ($fks) {
                if (in_array('user_id', $fks, true)) {
                    $table->dropForeign(['user_id']);
                }
                if (in_array('vendor_profile_id', $fks, true)) {
                    $table->dropForeign(['vendor_profile_id']);
                }
            });
        }

        if (Schema::hasTable('vendor_reviews')) {
            $fks = collect(Schema::getForeignKeys('vendor_reviews'))->pluck('columns')->flatten()->all();
            Schema::table('vendor_reviews', function (Blueprint $table) use ($fks) {
                if (in_array('order_id', $fks, true)) {
                    $table->dropForeign(['order_id']);
                }
                if (in_array('user_id', $fks, true)) {
                    $table->dropForeign(['user_id']);
                }
                if (in_array('vendor_profile_id', $fks, true)) {
                    $table->dropForeign(['vendor_profile_id']);
                }
            });
        }

        if (Schema::hasTable('event_reviews')) {
            $fks = collect(Schema::getForeignKeys('event_reviews'))->pluck('columns')->flatten()->all();
            Schema::table('event_reviews', function (Blueprint $table) use ($fks) {
                if (in_array('order_id', $fks, true)) {
                    $table->dropForeign(['order_id']);
                }
                if (in_array('user_id', $fks, true)) {
                    $table->dropForeign(['user_id']);
                }
                if (in_array('event_id', $fks, true)) {
                    $table->dropForeign(['event_id']);
                }
            });
        }
    }
};
