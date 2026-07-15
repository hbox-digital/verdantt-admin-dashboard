@extends(config('platform.workspace', 'platform::workspace.compact'))

@section('aside')
    <div class="aside col-xs-12 col-lg-3 col-xl-2 bg-dark d-flex flex-column" data-controller="menu" data-bs-theme="dark">
        {{--
            No logo banner here — the profile card just below already
            identifies the app/user, so a second brand block on top of it
            was redundant. The mobile menu toggler still needs to render
            (it's the only way to open the nav on small screens), just
            without the header-brand logo link next to it.
        --}}
        <header class="p-3 d-lg-none w-100 d-flex align-items-center">
            <a href="#" class="header-toggler d-flex align-items-center lh-1 link-body-emphasis"
               data-action="click->menu#toggle">
                <x-orchid-icon path="bs.three-dots-vertical" class="icon-menu"/>

                <span class="ms-2">@yield('title')</span>
            </a>
        </header>

        <nav class="aside-collapse w-100 d-lg-flex flex-column collapse-horizontal text-body-emphasis" id="headerMenuCollapse">

            @includeWhen(Auth::check(), 'platform::partials.profile')

            @include('platform::partials.search')

            <ul class="nav nav-pills flex-column mb-md-1 mb-auto ps-0 gap-1">
                {!! Dashboard::renderMenu() !!}

                <li class="nav-item">
                    <form method="POST" action="{{ route('platform.logout') }}">
                        @csrf
                        <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                            <x-orchid-icon path="bs.box-arrow-right" class="overflow-visible"/>
                            <span class="text-break">{{ __('Logout') }}</span>
                        </button>
                    </form>
                </li>
            </ul>

            <div class="h-100 w-100 position-relative to-top cursor d-none d-md-flex mt-md-5"
                 data-action="click->html-load#goToTop"
                 title="{{ __('Scroll to top') }}">
                <div class="bottom-left w-100 mb-2 ps-3 overflow-hidden">
                    <small data-controller="viewport-entrance-toggle"
                           class="scroll-to-top d-flex align-items-center gap-3"
                           data-viewport-entrance-toggle-class="show">
                        <x-orchid-icon path="bs.chevron-up"/>
                        {{ __('Scroll to top') }}
                    </small>
                </div>
            </div>
        </nav>
    </div>
@endsection

@section('workspace')
    @if(Breadcrumbs::has())
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb px-4 mb-2">
                <x-tabuna-breadcrumbs
                    class="breadcrumb-item"
                    active="active"
                />
            </ol>
        </nav>
    @endif

    <div class="order-last order-md-0 command-bar-wrapper">
        <div class="@hasSection('navbar') @else d-none d-md-block @endif layout d-md-flex align-items-center">
            <header class="d-none d-md-block col-xs-12 col-md p-0 me-3">
                <h1 class="m-0 fw-light h3 text-body-emphasis">@yield('title')</h1>
                <small class="text-muted" title="@yield('description')">@yield('description')</small>
            </header>
            <nav class="col-xs-12 col-md-auto ms-md-auto p-0">
                <ul class="nav command-bar justify-content-sm-end justify-content-start d-flex align-items-center gap-2 flex-wrap-reverse flex-sm-nowrap">
                    @yield('navbar')
                </ul>
            </nav>
        </div>
    </div>

    @include('platform::partials.alert')
    @yield('content')
@endsection
