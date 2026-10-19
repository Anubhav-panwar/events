<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->index()->after('role');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
            $table->foreignId('blocked_by_id')->nullable()->after('blocked_reason')->constrained('users')->nullOnDelete();
        });

        Schema::create('user_block_records', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('user_block_records');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by_id']);
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_reason', 'blocked_by_id']);
        });
    }
};
