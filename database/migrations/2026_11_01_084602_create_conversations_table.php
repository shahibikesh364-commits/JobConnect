<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_seeker_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('employer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('job_listing_id')
                ->nullable()
                ->constrained('job_listings')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('job_seeker_id');
            $table->index('employer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
