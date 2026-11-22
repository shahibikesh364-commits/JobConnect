<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the job seeker's profile.
     */
    public function show(): View
    {
        $user = request()->user();

        $profile = $user->jobSeekerProfile;
        $education = $user->education;
        $workExperiences = $user->workExperiences;
        $skills = $user->skills;

        $completion = $user->profileCompletion();

        return view('job-seeker.profile.show', compact(
            'user',
            'profile',
            'education',
            'workExperiences',
            'skills',
            'completion'
        ));
    }

    public function edit(Request $request): View
    {
        return view('job-seeker.profile.edit', [
            'user' => $request->user(),
            'profile' => $request->user()->jobSeekerProfile,
            'education' => $request->user()->education,
            'workExperiences' => $request->user()->workExperiences,
            'skills' => $request->user()->skills->pluck('name')->implode(', '),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:1000'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'headline' => ['nullable', 'string', 'max:160'],
            'about_me' => ['nullable', 'string', 'max:5000'],
            'career_objective' => ['nullable', 'string', 'max:5000'],
            'experience_level' => ['nullable', 'string', 'max:80'],
            'employment_status' => ['nullable', 'string', 'max:80'],
            'preferred_job_type' => ['nullable', 'string', 'max:80'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'education' => ['nullable', 'array', 'max:20'],
            'education.*.id' => ['nullable', 'integer', Rule::exists('education', 'id')->where(fn ($query) => $query->where('user_id', $user->id))],
            'education.*.degree' => ['nullable', 'required_with:education.*.institution', 'string', 'max:255'],
            'education.*.institution' => ['nullable', 'required_with:education.*.degree', 'string', 'max:255'],
            'education.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'education.*.graduation_year' => ['nullable', 'integer', 'min:1950', 'max:' . (now()->year + 10)],
            'education.*.description' => ['nullable', 'string', 'max:3000'],
            'education.*.delete' => ['nullable', 'boolean'],
            'work_experiences' => ['nullable', 'array', 'max:20'],
            'work_experiences.*.id' => ['nullable', 'integer', Rule::exists('work_experiences', 'id')->where(fn ($query) => $query->where('user_id', $user->id))],
            'work_experiences.*.company_name' => ['nullable', 'required_with:work_experiences.*.job_title', 'string', 'max:255'],
            'work_experiences.*.job_title' => ['nullable', 'required_with:work_experiences.*.company_name', 'string', 'max:255'],
            'work_experiences.*.employment_type' => ['nullable', 'string', 'max:80'],
            'work_experiences.*.start_date' => ['nullable', 'date'],
            'work_experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:work_experiences.*.start_date'],
            'work_experiences.*.currently_working' => ['nullable', 'boolean'],
            'work_experiences.*.description' => ['nullable', 'string', 'max:3000'],
            'work_experiences.*.delete' => ['nullable', 'boolean'],
            'skills' => ['nullable', 'string', 'max:3000'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        $profile = $user->jobSeekerProfile()->firstOrNew();
        $profile->user_id = $user->id;
        $oldPhoto = $profile->profile_photo;
        $oldResume = $profile->resume_path;
        $profileFields = [
            'phone', 'address', 'date_of_birth', 'headline', 'about_me',
            'career_objective', 'experience_level', 'employment_status',
            'preferred_job_type', 'linkedin_url', 'github_url', 'portfolio_url',
        ];
        $profile->fill(collect($validated)->only($profileFields)->all());

        if ($request->hasFile('profile_photo')) {
            $profile->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }
        if ($request->hasFile('resume')) {
            $resume = $request->file('resume');
            $profile->resume_path = $resume->store('resumes', 'public');
            $profile->resume_original_name = $resume->getClientOriginalName();
        }

        $profile->save();

        foreach ($validated['education'] ?? [] as $entry) {
            if (filter_var($entry['delete'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (filled($entry['id'] ?? null)) {
                    $user->education()->findOrFail($entry['id'])->delete();
                }
                continue;
            }
            if (blank($entry['degree'] ?? null) && blank($entry['institution'] ?? null)) {
                continue;
            }
            $record = filled($entry['id'] ?? null)
                ? $user->education()->findOrFail($entry['id'])
                : $user->education()->make();
            $record->fill(collect($entry)->only([
                'degree', 'institution', 'field_of_study', 'graduation_year', 'description',
            ])->all())->save();
        }

        foreach ($validated['work_experiences'] ?? [] as $entry) {
            if (filter_var($entry['delete'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (filled($entry['id'] ?? null)) {
                    $user->workExperiences()->findOrFail($entry['id'])->delete();
                }
                continue;
            }
            if (blank($entry['job_title'] ?? null) && blank($entry['company_name'] ?? null)) {
                continue;
            }
            $record = filled($entry['id'] ?? null)
                ? $user->workExperiences()->findOrFail($entry['id'])
                : $user->workExperiences()->make();
            $record->fill(collect($entry)->only([
                'company_name', 'job_title', 'employment_type', 'start_date',
                'end_date', 'currently_working', 'description',
            ])->all())->save();
        }

        $skillNames = collect(explode(',', $validated['skills'] ?? ''))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->take(40);
        $skillIds = $skillNames->map(fn ($name) => Skill::firstOrCreate(
            ['name' => $name],
            ['category' => 'General'],
        )->id);
        $user->skills()->sync($skillIds);

        if ($oldPhoto && $oldPhoto !== $profile->profile_photo) {
            Storage::disk('public')->delete($oldPhoto);
        }
        if ($oldResume && $oldResume !== $profile->resume_path) {
            Storage::disk('public')->delete($oldResume);
        }

        return redirect()->route('job-seeker.profile.show');
    }
}
