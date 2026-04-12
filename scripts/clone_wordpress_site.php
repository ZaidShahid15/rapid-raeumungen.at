<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$baseUrl = 'https://rapid-raeumungen.at';
$baseHost = parse_url($baseUrl, PHP_URL_HOST);

$tempHtmlDir = $projectRoot . '/public/downloaded-html';
$viewsDir = $projectRoot . '/resources/views/pages';
$layoutDir = $projectRoot . '/resources/views/layouts';
$publicDir = $projectRoot . '/public';
$reportFile = $projectRoot . '/storage/app/site-clone-report.json';

$pageUrls = [];
$assetUrls = [];
$downloadedAssets = [];
$visitedSitemaps = [];
$failedPages = [];
$failedAssets = [];

ensureDirectory($tempHtmlDir);
ensureDirectory($viewsDir);
ensureDirectory($layoutDir);
ensureDirectory(dirname($reportFile));

ensureDirectory($tempHtmlDir);
cleanGeneratedViews($viewsDir);

collectSitemapUrls($baseUrl . '/wp-sitemap.xml');

ksort($pageUrls);

foreach (array_keys($pageUrls) as $pageUrl) {
    $tempHtmlPath = $tempHtmlDir . pageUrlToRelativeFile($pageUrl);
    $html = is_file($tempHtmlPath) ? file_get_contents($tempHtmlPath) : fetchText($pageUrl);

    if ($html === null) {
        $failedPages[] = $pageUrl;
        continue;
    }

    ensureDirectory(dirname($tempHtmlPath));
    file_put_contents($tempHtmlPath, $html);

    collectAssetsFromHtml($html, $pageUrl);
    generateBladeFromHtml($pageUrl, $html);
}

$assetQueue = array_keys($assetUrls);

while ($assetQueue !== []) {
    $assetUrl = array_shift($assetQueue);

    if (isset($downloadedAssets[$assetUrl])) {
        continue;
    }

    $localAssetPath = assetUrlToLocalPath($assetUrl);

    if ($localAssetPath === null) {
        $downloadedAssets[$assetUrl] = false;
        continue;
    }

    $binary = fetchBinary($assetUrl);

    if ($binary === null) {
        $failedAssets[] = $assetUrl;
        $downloadedAssets[$assetUrl] = false;
        continue;
    }

    $targetPath = $publicDir . '/' . ltrim($localAssetPath, '/');
    ensureDirectory(dirname($targetPath));

    if (is_file($targetPath) && filesize($targetPath) > 0) {
        $downloadedAssets[$assetUrl] = true;

        if (isCssPath($localAssetPath)) {
            $cssAssets = collectAssetsFromCss((string) file_get_contents($targetPath), $assetUrl);

            foreach ($cssAssets as $cssAssetUrl) {
                if (!isset($assetUrls[$cssAssetUrl])) {
                    $assetUrls[$cssAssetUrl] = true;
                    $assetQueue[] = $cssAssetUrl;
                }
            }
        }

        continue;
    }

    file_put_contents($targetPath, $binary);
    $downloadedAssets[$assetUrl] = true;

    if (isCssPath($localAssetPath)) {
        $cssAssets = collectAssetsFromCss($binary, $assetUrl);

        foreach ($cssAssets as $cssAssetUrl) {
            if (!isset($assetUrls[$cssAssetUrl])) {
                $assetUrls[$cssAssetUrl] = true;
                $assetQueue[] = $cssAssetUrl;
            }
        }
    }
}

rrmdir($tempHtmlDir);

$report = [
    'pages' => count($pageUrls),
    'assets' => count($assetUrls),
    'failed_pages' => array_values(array_unique($failedPages)),
    'failed_assets' => array_values(array_unique($failedAssets)),
];

file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo 'Cloned ' . $report['pages'] . ' pages and collected ' . $report['assets'] . " assets.\n";
echo 'Report: ' . $reportFile . "\n";

function collectSitemapUrls(string $sitemapUrl): void
{
    global $visitedSitemaps, $pageUrls;

    if (isset($visitedSitemaps[$sitemapUrl])) {
        return;
    }

    $visitedSitemaps[$sitemapUrl] = true;
    $xmlString = fetchText($sitemapUrl);

    if ($xmlString === null) {
        return;
    }

    $xml = @simplexml_load_string($xmlString);

    if (!$xml instanceof SimpleXMLElement) {
        return;
    }

    $rootName = $xml->getName();

    if ($rootName === 'sitemapindex') {
        foreach ($xml->sitemap as $sitemap) {
            $loc = trim((string) $sitemap->loc);

            if ($loc !== '') {
                collectSitemapUrls($loc);
            }
        }

        return;
    }

    if ($rootName === 'urlset') {
        foreach ($xml->url as $urlNode) {
            $loc = trim((string) $urlNode->loc);

            if ($loc !== '') {
                $pageUrls[$loc] = true;
            }

            $imageChildren = $urlNode->children('http://www.google.com/schemas/sitemap-image/1.1');

            foreach ($imageChildren as $imageNode) {
                $imageLoc = trim((string) $imageNode->loc);

                if ($imageLoc !== '') {
                    rememberAssetUrl($imageLoc);
                }
            }
        }
    }
}

function generateBladeFromHtml(string $pageUrl, string $html): void
{
    global $viewsDir;

    [$htmlAttributes, $head, $bodyAttributes, $body] = extractHtmlParts($html);

    $head = rewriteDocumentFragment($head, $pageUrl);
    $body = rewriteDocumentFragment($body, $pageUrl);
    $htmlAttributes = rewriteLooseContent($htmlAttributes, $pageUrl);
    $bodyAttributes = rewriteLooseContent($bodyAttributes, $pageUrl);

    $bladePath = $viewsDir . '/' . pageUrlToViewPath($pageUrl) . '.blade.php';
    ensureDirectory(dirname($bladePath));

    $blade = <<<BLADE
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

    file_put_contents($bladePath, $blade);
}

function extractHtmlParts(string $html): array
{
    $htmlAttributes = '';
    $head = '';
    $bodyAttributes = '';
    $body = $html;

    if (preg_match('/<html\b([^>]*)>/is', $html, $match)) {
        $htmlAttributes = trim($match[1]);
    }

    if (preg_match('/<head\b[^>]*>(.*)<\/head>/isUs', $html, $match)) {
        $head = trim($match[1]);
    }

    if (preg_match('/<body\b([^>]*)>(.*)<\/body>/isUs', $html, $match)) {
        $bodyAttributes = trim($match[1]);
        $body = trim($match[2]);
    }

    return [$htmlAttributes, $head, $bodyAttributes, $body];
}

function collectAssetsFromHtml(string $html, string $pageUrl): void
{
    $attributes = ['href', 'src', 'srcset', 'poster', 'data-src', 'data-lazy-src', 'data-thumb', 'content'];

    foreach ($attributes as $attribute) {
        if (!preg_match_all('/\b' . preg_quote($attribute, '/') . '\s*=\s*([\'"])(.*?)\1/is', $html, $matches)) {
            continue;
        }

        foreach ($matches[2] as $rawValue) {
            collectUrlsFromAttributeValue($rawValue, $pageUrl);
        }
    }

    if (preg_match_all('/url\((["\']?)(.*?)\1\)/is', $html, $matches)) {
        foreach ($matches[2] as $rawValue) {
            rememberAssetUrl(resolveUrl($rawValue, $pageUrl));
        }
    }
}

function collectAssetsFromCss(string $css, string $cssUrl): array
{
    $found = [];

    if (preg_match_all('/url\((["\']?)(.*?)\1\)/is', $css, $matches)) {
        foreach ($matches[2] as $rawValue) {
            $resolved = resolveUrl($rawValue, $cssUrl);

            if ($resolved !== null && isLocalAssetUrl($resolved)) {
                $found[$resolved] = true;
            }
        }
    }

    return array_keys($found);
}

function collectUrlsFromAttributeValue(string $value, string $pageUrl): void
{
    $parts = preg_split('/\s*,\s*/', $value);

    foreach ($parts as $part) {
        $url = trim((string) preg_split('/\s+/', trim($part))[0]);
        rememberAssetUrl(resolveUrl($url, $pageUrl));
    }
}

function rememberAssetUrl(?string $url): void
{
    global $assetUrls;

    if ($url === null || !isLocalAssetUrl($url)) {
        return;
    }

    $assetUrls[$url] = true;
}

function rewriteDocumentFragment(string $html, string $pageUrl): string
{
    $html = preg_replace_callback(
        '/https?:\/\/rapid-raeumungen\.at[^"\')\s<]*/i',
        static fn(array $matches): string => localizeUrl($matches[0]),
        $html
    );

    return preg_replace_callback(
        '/\/\/rapid-raeumungen\.at[^"\')\s<]*/i',
        static fn(array $matches): string => localizeUrl('https:' . $matches[0]),
        $html
    );
}

function rewriteLooseContent(string $content, string $pageUrl): string
{
    return rewriteDocumentFragment($content, $pageUrl);
}

function localizeUrl(string $url): string
{
    $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5);
    $parsed = parse_url($url);

    if ($parsed === false) {
        return $url;
    }

    $host = $parsed['host'] ?? null;
    $path = $parsed['path'] ?? '/';
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

    if ($host !== null && strtolower($host) !== 'rapid-raeumungen.at') {
        return $url;
    }

    if (looksLikeAssetPath($path)) {
        return $path;
    }

    if ($path === '' || $path === '/') {
        return '/' . $fragment;
    }

    return rtrim($path, '/') . '/' . $query . $fragment;
}

function resolveUrl(string $url, string $baseUrl): ?string
{
    $trimmed = trim($url);

    if ($trimmed === '' || str_starts_with($trimmed, 'data:') || str_starts_with($trimmed, 'mailto:') || str_starts_with($trimmed, 'tel:') || str_starts_with($trimmed, 'javascript:') || str_starts_with($trimmed, '#')) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $trimmed)) {
        return $trimmed;
    }

    if (str_starts_with($trimmed, '//')) {
        return 'https:' . $trimmed;
    }

    if (str_starts_with($trimmed, '/')) {
        return 'https://rapid-raeumungen.at' . $trimmed;
    }

    $basePath = parse_url($baseUrl, PHP_URL_PATH) ?: '/';
    $baseDirectory = preg_replace('#/[^/]*$#', '/', $basePath);
    $joined = $baseDirectory . $trimmed;

    $segments = [];

    foreach (explode('/', $joined) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }

        if ($segment === '..') {
            array_pop($segments);
            continue;
        }

        $segments[] = $segment;
    }

    return 'https://rapid-raeumungen.at/' . implode('/', $segments);
}

function isLocalAssetUrl(string $url): bool
{
    $parsed = parse_url($url);

    if ($parsed === false) {
        return false;
    }

    $host = strtolower((string) ($parsed['host'] ?? 'rapid-raeumungen.at'));

    if ($host !== 'rapid-raeumungen.at') {
        return false;
    }

    return looksLikeAssetPath((string) ($parsed['path'] ?? '/'));
}

function looksLikeAssetPath(string $path): bool
{
    if (str_starts_with($path, '/wp-content/') || str_starts_with($path, '/wp-includes/')) {
        return true;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return in_array($extension, [
        'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif', 'ico', 'woff', 'woff2', 'ttf', 'otf',
        'eot', 'json', 'xml', 'mp4', 'webm', 'mp3', 'wav', 'pdf'
    ], true);
}

function assetUrlToLocalPath(string $url): ?string
{
    $parsed = parse_url($url);

    if ($parsed === false) {
        return null;
    }

    $path = $parsed['path'] ?? null;

    if ($path === null || $path === '') {
        return null;
    }

    return $path;
}

function pageUrlToRelativeFile(string $pageUrl): string
{
    $parsed = parse_url($pageUrl);
    $path = trim((string) ($parsed['path'] ?? '/'), '/');

    if ($path === '') {
        return '/index.html';
    }

    return '/' . $path . '/index.html';
}

function pageUrlToViewPath(string $pageUrl): string
{
    $parsed = parse_url($pageUrl);
    $path = trim((string) ($parsed['path'] ?? '/'), '/');

    if ($path === '') {
        return 'index';
    }

    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function fetchText(string $url): ?string
{
    $content = fetchBinary($url);

    return $content === null ? null : (string) $content;
}

function fetchBinary(string $url): ?string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: LaravelCloneBot/1.0\r\nAccept: */*\r\n",
            'timeout' => 45,
            'follow_location' => 1,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $content = @file_get_contents($url, false, $context);

    if ($content === false) {
        return null;
    }

    return $content;
}

function ensureDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

function cleanDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    rrmdir($directory);
    mkdir($directory, 0777, true);
}

function cleanGeneratedViews(string $directory): void
{
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
        return;
    }

    $items = scandir($directory);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
}

function rrmdir(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $items = scandir($directory);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;

        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }

    rmdir($directory);
}

function isCssPath(string $path): bool
{
    return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'css';
}
