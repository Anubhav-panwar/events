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
            foreach (['vendor_profiles', 'users', 'vendor_share_clicks'] as $t) {
                if (Schema::hasTable($t)) {
                    try {
                        DB::statement("ALTER TABLE `{$t}` ENGINE = InnoDB");
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (!Schema::hasTable('vendor_share_clicks')) {
            Schema::create('vendor_share_clicks', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('channel');
                $table->text('target_url')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['vendor_profile_id', 'channel']);
            });
        } else {
            Schema::table('vendor_share_clicks', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_share_clicks', 'vendor_profile_id')) {
                    $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'channel')) {
                    $table->string('channel');
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'target_url')) {
                    $table->text('target_url')->nullable();
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
                if (!Schema::hasColumn('vendor_share_clicks', 'created_at')) {
                    $table->timestamp('created_at')->useCurrent();
                }
            });

            $existingFks = collect(Schema::getForeignKeys('vendor_share_clicks'))->pluck('columns')->flatten()->all();
            Schema::table('vendor_share_clicks', function (Blueprint $table) use ($existingFks) {
                if (!in_array('vendor_profile_id', $existingFks, true) && Schema::hasColumn('vendor_share_clicks', 'vendor_profile_id')) {
                    $table->foreign('vendor_profile_id')->references('id')->on('vendor_profiles')->cascadeOnDelete();
                }
                if (!in_array('user_id', $existingFks, true) && Schema::hasColumn('vendor_share_clicks', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_share_clicks');
    }
};
