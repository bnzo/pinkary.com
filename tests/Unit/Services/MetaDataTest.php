<?php

declare(strict_types=1);

use App\Services\MetaData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

mutates(MetaData::class);

it('returns the cached meta data if it exists', function () {
    $url = 'https://laravel.com';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $cachedData = collect([
        'title' => 'Laravel - The PHP Framework For Web Artisans',
        'description' => 'Laravel is a PHP web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.',
        'type' => 'website',
        'url' => 'https://laravel.com/',
        'image' => 'https://laravel.com/img/og-image.jpg',
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->toArray())->toBe($cachedData->toArray());
});

it('gets the youtube oembed data', function () {
    $url = 'https://youtu.be/emMYyeBfYlM';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('title'))->toBe('Migrating Brent’s PHPUnit Test Suite to Pest')
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('gets the twitter oembed data', function () {
    $url = 'https://x.com/enunomaduro/status/1845794776886493291';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('type'))->toBe('rich')
        ->and($data->has('html'))->toBeTrue();
});

it('gets the vimdeo oembed data', function () {
    $url = 'https://vimeo.com/76979871';
    $cacheKey = Str::of($url)->slug()->prepend('preview_')->value();
    $service = new MetaData($url);
    $data = $service->fetch();

    expect(Cache::get($cacheKey))->toBe($data)
        ->and($data->get('type'))->toBe('video')
        ->and($data->has('html'))->toBeTrue();
});

it('returns an empty collection if the HTTP request fails', function () {
    $url = 'https://aurlthatdoesnotexist.com';

    Http::fake([
        $url => Http::response('', 404),
    ]);

    $service = new MetaData($url);
    $data = $service->fetch();

    expect($data->isEmpty())->toBeTrue();
});
