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
                $existingUsersFks = collect(Schema::getForeignKeys('users'))->pluck('columns')->flatten()->all();
                if (!in_array('blocked_by_id', $existingUsersFks, true)) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->foreign('blocked_by_id')->references('id')->on('users')->nullOnDelete();
                    });
                }
            }
        }

        if (!Schema::hasTable('user_block_records')) {
            Schema::create('user_block_records', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action'); // 'blocked' or 'unblocked'
                $table->text('reason')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        } else {
            if ($isMySql) {
                DB::statement("ALTER TABLE `user_block_records` ENGINE = InnoDB");
            }

            Schema::table('user_block_records', function (Blueprint $table) {
                if (!Schema::hasColumn('user_block_records', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('user_block_records', 'admin_id')) {
                    $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('user_block_records', 'action')) {
                    $table->string('action');
                }
                if (!Schema::hasColumn('user_block_records', 'reason')) {
                    $table->text('reason')->nullable();
                }
                if (!Schema::hasColumn('user_block_records', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
                if (!Schema::hasColumn('user_block_records', 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
            });

            $existingFks = collect(Schema::getForeignKeys('user_block_records'))->pluck('columns')->flatten()->all();
            Schema::table('user_block_records', function (Blueprint $table) use ($existingFks) {
                if (!in_array('user_id', $existingFks, true) && Schema::hasColumn('user_block_records', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                if (!in_array('admin_id', $existingFks, true) && Schema::hasColumn('user_block_records', 'admin_id')) {
                    $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_block_records');

        if (Schema::hasTable('users')) {
            $userColsToDrop = [];
            $existingFks = collect(Schema::getForeignKeys('users'))->pluck('columns')->flatten()->all();

            Schema::table('users', function (Blueprint $table) use ($existingFks, &$userColsToDrop) {
                if (in_array('blocked_by_id', $existingFks, true)) {
                    $table->dropForeign(['blocked_by_id']);
                }
                foreach (['blocked_by_id', 'blocked_reason', 'blocked_at', 'is_blocked'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $userColsToDrop[] = $col;
                    }
                }
                if (!empty($userColsToDrop)) {
                    $table->dropColumn($userColsToDrop);
                }
            });
        }
    }
};
