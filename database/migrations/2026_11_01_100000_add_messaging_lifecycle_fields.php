<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('job_seeker_archived_at')->nullable();
            $table->timestamp('employer_archived_at')->nullable();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes();
        });

        // Merge any legacy duplicate job conversations without losing messages.
        $duplicates = DB::table('conversations')
            ->selectRaw('MIN(id) as keep_id, job_seeker_id, employer_id, job_listing_id')
            ->groupBy('job_seeker_id', 'employer_id', 'job_listing_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $query = DB::table('conversations')
                ->where('job_seeker_id', $duplicate->job_seeker_id)
                ->where('employer_id', $duplicate->employer_id);

            $duplicate->job_listing_id === null
                ? $query->whereNull('job_listing_id')
                : $query->where('job_listing_id', $duplicate->job_listing_id);

            $duplicateIds = (clone $query)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('id');

            if ($duplicateIds->isNotEmpty()) {
                DB::table('messages')->whereIn('conversation_id', $duplicateIds)
                    ->update(['conversation_id' => $duplicate->keep_id]);
                DB::table('conversations')->whereIn('id', $duplicateIds)->delete();
            }
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->unique(
                ['job_seeker_id', 'employer_id', 'job_listing_id'],
                'conversations_participants_job_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_participants_job_unique');
            $table->dropColumn(['job_seeker_archived_at', 'employer_archived_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('edited_at');
        });
    }
};
