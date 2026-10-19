<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('tier', 30)->nullable()->default('custom')->after('name');
            $table->text('description')->nullable()->after('tier');
            $table->boolean('is_active')->default(true)->after('sales_end')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['tier', 'description', 'is_active']);
        });
    }
};
