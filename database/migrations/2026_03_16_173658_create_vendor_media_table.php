<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('type')->default('image'); // image|video
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['vendor_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_media');
    }
};
