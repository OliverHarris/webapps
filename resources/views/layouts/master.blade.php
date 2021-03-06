<!DOCTYPE html>
<html style="height:100%">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Toogle - {{ $title ?? ''}}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">


     <!-- CSRF Token -->
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script> --}}

</head>

<body style="height:100%">


    <nav class="navbar navbar-expand-lg navbar-light">

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">

                @if (!Request::is("/"))
                <a href="/">
                <h1>Toogle</h1>
                </a>
                @endif

            </ul>
            <div class="my-2 my-lg-0">
                @if (!($hideSearch ?? false))
                <form class="form-inline" action="/tags" method="GET">
                    <input class="form-control" type="search" name="search" placeholder="Search tag">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                @endif
                @if (Route::has('login'))
                @auth
                <a href="{{ url('/logout') }}">Logout </a>
                <a href="{{ url('/post') }}"> Create post </a>
                <a href="/account"> Your account </a>
                @if (Auth::user()->admin)
                <a href="/admin/users"> USERS </a>
                <a href="/admin/tags"> TAGS </a>
                @endif
                @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}">Register</a>
                @endif
                @endauth
                @endif
            </div>
        </div>
    </nav>
<hr>
    <div class="container" style="height:100%">
        @yield("content")
    </div>



</body>