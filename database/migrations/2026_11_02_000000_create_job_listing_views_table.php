<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listing_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->timestamps();

            $table->unique(['job_listing_id', 'visitor_hash'], 'job_listing_views_unique_visitor');
            $table->index(['job_listing_id', 'created_at'], 'job_listing_views_job_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listing_views');
    }
};
