<?php

declare(strict_types=1);

$viewsDir = dirname(__DIR__) . '/resources/views/pages';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, FilesystemIterator::SKIP_DOTS)
);

$updated = 0;

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    $content = file_get_contents($path);

    if ($content === false || str_contains($content, '@verbatim')) {
        continue;
    }

    $pattern = "/@section\\('html_attributes'\\)(.*?)@endsection\\s*@section\\('head'\\)\\s*(.*?)\\s*@endsection\\s*@section\\('body_attributes'\\)(.*?)@endsection\\s*@section\\('content'\\)\\s*(.*?)\\s*@endsection\\s*$/s";

    if (!preg_match($pattern, $content, $matches)) {
        continue;
    }

    $htmlAttributes = trim($matches[1]);
    $head = trim($matches[2]);
    $bodyAttributes = trim($matches[3]);
    $body = trim($matches[4]);

    $rebuilt = <<<BLADE
@extends('layouts.site')

@section('html_attributes')
@verbatim
{$htmlAttributes}
@endverbatim
@endsection

@section('head')
@verbatim
{$head}
@endverbatim
@endsection

@section('body_attributes')
@verbatim
{$bodyAttributes}
@endverbatim
@endsection

@section('content')
@verbatim
{$body}
@endverbatim
@endsection

BLADE;

    file_put_contents($path, $rebuilt);
    $updated++;
}

echo "Updated {$updated} generated Blade views.\n";
