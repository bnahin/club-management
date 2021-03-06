<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.header.meta')
    <title>
        {{ config('app.school-name')." ". ($clubName ?: 'Club Management') }}
        | @yield('page-title', 'Home')
    </title>
    @include('partials.header.styles')
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ $clubName }} @if(isAdmin()) Management @else Timecard @endif @if(app()->isLocal()) [DEV] @endif
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Nav Links -->
                    <li class="nav-item {{ (Route::currentRouteName() == "home") ? "active":"" }}">
                        <a class="nav-link" href="/"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item {{ (Route::currentRouteName() == "view-announcements") ? "active":"" }}">
                        <a class="nav-link" href="#"
                           onclick="swal('Coming Soon!', 'Club announcements are currently in development.', 'info')"><i
                                class="fas fa-comment"></i> Announcements
                            @if($announcementsBadge)
                                <span class="badge badge-info">{{ $announcementsBadge }}</span>
                            @endif
                        </a>
                    </li>
                    @auth('user')
                        <li class="nav-item {{ (Route::currentRouteName() == "my-hours") ? "active":"" }}">
                            <a class="nav-link" href=" {{ route('my-hours') }}"><i class="fas fa-clock"></i> My
                                Hours</a>
                        </li>
                    @elseauth('admin')
                        <li class="nav-item {{ (Route::currentRouteName() == "admin") ? "active":"" }}">
                            <a class="nav-link" href="{{ route('admin') }}"><i
                                    class="fas fa-cogs"></i> Admin
                                @if($adminBadge) <span
                                    class="badge badge-success marked-badge">{{ $adminBadge }}</span> @endif
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-auth="{{ (isAdmin()) ? "admin" : "user" }}"
                           onclick="swal('App Support', 'For technical inquiries, contact Blake Nahin, bnahin@live.com. For all others, contact your club leaders.','info');"><i
                                class="fas fa-life-ring"></i> Help</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ (Route::currentRouteName() == "my-clubs") ? "active":"" }}"
                           href="#" id="navbarDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i> {{ Auth::user()->full_name }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item {{ (Route::currentRouteName() == "my-clubs") ? "active":"" }}"
                               href="{{ route('my-clubs') }}">
                                <i class="fas fa-graduation-cap"></i> My Clubs - @admin Admin @else Student @endadmin
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}" style="color:red">
                                <strong> <i class="fas fa-sign-out-alt"></i>
                                    Sign Out/Switch Club</strong></a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>
</div>

@include('partials.footer.footer')
</body>
@include('partials.footer.scripts')
</html>
