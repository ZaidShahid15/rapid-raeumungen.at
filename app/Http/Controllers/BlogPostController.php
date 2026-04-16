<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function show(string $slug, Request $request): View
    {
        $post = $this->findPostBySlug($slug);

        abort_unless($post !== null, 404);

        return view('pages.blog-post', [
            'post' => $post,
            'canonicalUrl' => $request->url(),
        ]);
    }

    private function findPostBySlug(string $slug): ?array
    {
        foreach ($this->extractPostsFromBlogListing() as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Build blog post records from the static blog listing page so linked posts
     * can open locally even when dedicated WordPress post templates are absent.
     *
     * @return array<int, array<string, string>>
     */
    private function extractPostsFromBlogListing(): array
    {
        $blogViewPath = resource_path('views/pages/blog.blade.php');

        if (! is_file($blogViewPath)) {
            return [];
        }

        $contents = file_get_contents($blogViewPath);

        if ($contents === false) {
            return [];
        }

        preg_match_all(
            '/<article\b.*?<\/article>/is',
            $contents,
            $articleMatches
        );

        $posts = [];

        foreach ($articleMatches[0] as $articleHtml) {
            if (! preg_match('/<h2\b[^>]*>.*?<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is', $articleHtml, $titleMatch)) {
                continue;
            }

            $path = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5), '/');

            if ($path === '' || str_contains($path, '/')) {
                continue;
            }

            preg_match('/<div class="wpr-grid-item-date.*?<span>([^<]+)<span/is', $articleHtml, $dateMatch);
            preg_match('/<div class="wpr-grid-item-excerpt.*?<p>(.*?)<\/p>/is', $articleHtml, $excerptMatch);

            $title = trim(strip_tags(html_entity_decode($titleMatch[2], ENT_QUOTES | ENT_HTML5)));
            $date = isset($dateMatch[1]) ? trim(strip_tags(html_entity_decode($dateMatch[1], ENT_QUOTES | ENT_HTML5))) : '';
            $excerpt = isset($excerptMatch[1]) ? trim(strip_tags(html_entity_decode($excerptMatch[1], ENT_QUOTES | ENT_HTML5))) : '';

            $posts[] = [
                'slug' => $path,
                'title' => $title,
                'date' => $date,
                'excerpt' => $excerpt,
            ];
        }

        return $posts;
    }
}
