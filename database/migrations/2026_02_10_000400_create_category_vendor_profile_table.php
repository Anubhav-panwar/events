<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_vendor_profile', function (Blueprint $table) {
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['vendor_profile_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_vendor_profile');
    }
};
