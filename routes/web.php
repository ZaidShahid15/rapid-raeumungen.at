<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::get('/{path?}', function (?string $path = null) {
    $view = $path === null || $path === ''
        ? 'pages.index'
        : 'pages.' . str_replace('/', '.', trim($path, '/'));

    abort_unless(View::exists($view), 404);

    return view($view);
})->where('path', '.*');
