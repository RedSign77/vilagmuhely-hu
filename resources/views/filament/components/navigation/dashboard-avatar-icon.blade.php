@php
    $user = auth()->user();
    $avatarUrl = $user && $user->avatar ? asset('storage/' . $user->avatar) : null;
@endphp

@if ($avatarUrl)
    <img
        src="{{ $avatarUrl }}"
        alt="{{ $user->name }}"
        class="w-5 h-5 rounded-full object-cover"
    />
@else
    <x-heroicon-o-home class="w-5 h-5" />
@endif
