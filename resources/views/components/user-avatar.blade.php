@props(['user', 'class' => 'user-avatar'])

@php
    $photo = $user?->jobSeekerProfile?->profile_photo;
    $name = $user?->name ?? 'User';
    $isCandidateAvatar = str_contains($class, 'employer-candidate-avatar');
    $avatarFrameStyle = $isCandidateAvatar
        ? 'width:46px!important;height:46px!important;min-width:46px;min-height:46px;flex:0 0 46px!important;aspect-ratio:1;overflow:hidden;border-radius:50%!important'
        : 'aspect-ratio:1;height:auto;overflow:hidden;border-radius:50%;flex-shrink:0';
@endphp

<span {{ $attributes->class($class) }} aria-hidden="true"
    style="{{ $avatarFrameStyle }}">
    @if ($photo)
        <img src="{{ asset('storage/' . $photo) }}" alt="" loading="lazy"
            style="display:block;width:100%;height:100%;aspect-ratio:1;object-fit:cover;border-radius:50%;clip-path:circle(50%)">
    @else
        {{ strtoupper(mb_substr($name, 0, 1)) }}
    @endif
</span>
