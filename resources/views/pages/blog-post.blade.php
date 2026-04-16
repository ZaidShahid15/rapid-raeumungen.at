@extends('layouts.site')

@section('html_attributes')
lang="en"
@endsection

@section('head')
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>{{ $post['title'] }} - Blog</title>
<meta name="description" content="{{ $post['excerpt'] }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f5f7fb;
        color: #142633;
    }

    .blog-post-page {
        max-width: 860px;
        margin: 0 auto;
        padding: 48px 20px 80px;
    }

    .blog-post-card {
        background: #fff;
        border-radius: 18px;
        padding: 32px;
        box-shadow: 0 18px 40px rgba(20, 38, 51, 0.08);
    }

    .blog-post-back {
        display: inline-block;
        margin-bottom: 24px;
        color: #008eff;
        text-decoration: none;
        font-weight: 700;
    }

    .blog-post-meta {
        margin: 0 0 12px;
        color: #6b7280;
        font-size: 14px;
    }

    .blog-post-title {
        margin: 0 0 20px;
        font-size: 34px;
        line-height: 1.2;
    }

    .blog-post-content {
        font-size: 18px;
        line-height: 1.8;
    }

    @media (max-width: 640px) {
        .blog-post-page {
            padding: 28px 16px 56px;
        }

        .blog-post-card {
            padding: 22px;
        }

        .blog-post-title {
            font-size: 28px;
        }
    }
</style>
@endsection

@section('content')
<main class="blog-post-page">
    <article class="blog-post-card">
        <a class="blog-post-back" href="/blog/">Back to Blog</a>
        @if ($post['date'] !== '')
            <p class="blog-post-meta">{{ $post['date'] }}</p>
        @endif
        <h1 class="blog-post-title">{{ $post['title'] }}</h1>
        <div class="blog-post-content">
            <p>{{ $post['excerpt'] !== '' ? $post['excerpt'] : 'This blog post is available from the local blog archive listing.' }}</p>
        </div>
    </article>
</main>
@endsection
