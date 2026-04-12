<!DOCTYPE html>
<html @yield('html_attributes')>
    <head>
        <meta name="robots" content="noindex, follow">
        @yield('head')
    </head>
    <body @yield('body_attributes')>
        @yield('content')
    </body>
</html>
