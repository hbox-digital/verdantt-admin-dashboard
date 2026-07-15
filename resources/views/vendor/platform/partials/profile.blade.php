{{--
    Matches the mobile app's account card: rounded card with a muted
    lighter-green background, brand icon avatar, name + email, and a
    trailing chevron linking to the profile screen. Logout now lives as
    its own item in the nav list below (see dashboard.blade.php), not
    inside this card.

    This renders outside Orchid's outer <form id="post-form"> (the aside
    is a sibling @section, not part of @yield('content')), so nothing
    here risks the nested-form issue other parts of the app had to avoid.
--}}
<a href="{{ route(config('platform.profile', 'platform.profile')) }}" class="profile-card d-flex align-items-center gap-3 rounded-4 p-3 mt-3 text-decoration-none">
    <img src="{{ asset('images/favicon.png') }}" alt="" class="rounded-circle" style="width: 2.75rem; height: 2.75rem;">

    <small class="d-flex flex-column lh-sm flex-grow-1 overflow-hidden">
        <span class="text-ellipsis text-white fw-bold">{{ Auth::user()->presenter()->title() }}</span>
        <span class="text-ellipsis text-white-50">{{ Auth::user()->email }}</span>
    </small>

    <x-orchid-icon path="bs.chevron-right" class="text-white-50"/>
</a>
