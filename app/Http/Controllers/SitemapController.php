<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $paths = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (LaravelRoute $route): bool => $this->isSitemapEligibleRoute($route))
            ->map(fn (LaravelRoute $route): string => $route->uri())
            ->merge($this->extractBlogPostPaths())
            ->map(fn (string $path): string => $this->normalizePath($path))
            ->unique()
            ->values();

        $xml = view('sitemap', [
            'baseUrl' => $baseUrl,
            'paths' => $paths,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function isSitemapEligibleRoute(LaravelRoute $route): bool
    {
        if (! in_array('GET', $route->methods(), true)) {
            return false;
        }

        $uri = $route->uri();

        if ($uri === 'sitemap.xml' || Str::contains($uri, ['{', '}'])) {
            return false;
        }

        return true;
    }

    /**
     * Build blog post paths from the static blog listing page.
     *
     * @return array<int, string>
     */
    private function extractBlogPostPaths(): array
    {
        $blogViewPath = resource_path('views/pages/blog.blade.php');

        if (! is_file($blogViewPath)) {
            return [];
        }

        $contents = file_get_contents($blogViewPath);

        if ($contents === false) {
            return [];
        }

        preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>/i', $contents, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $href): string => trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5)))
            ->filter(function (string $href): bool {
                if ($href === '' || Str::startsWith($href, ['#', 'mailto:', 'tel:'])) {
                    return false;
                }

                if (preg_match('#^https?://#i', $href) === 1) {
                    return false;
                }

                $path = trim(parse_url($href, PHP_URL_PATH) ?? '', '/');

                return $path !== '' && ! Str::contains($path, '/');
            })
            ->map(fn (string $href): string => trim(parse_url($href, PHP_URL_PATH) ?? '', '/'))
            ->values()
            ->all();
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path, '/');

        return $trimmed === '' ? '/' : '/' . $trimmed;
    }
}
