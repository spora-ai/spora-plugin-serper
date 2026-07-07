<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Spora\Plugins\Serper\Tools\SerperSearchTool;
use Spora\Services\ToolConfigService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

it('returns error if api key is missing', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn([]);

    $client = Mockery::mock(HttpClientInterface::class);
    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['q' => 'apple'], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('API key is not configured');
});

it('makes correct http request and parses organic and answer box results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'answerBox' => [
            'answer' => 'Steve Jobs',
        ],
        'organic' => [
            ['title' => 'Apple', 'link' => 'https://apple.com', 'snippet' => 'Tech company'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/search', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'apple';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'search', 'q' => 'apple'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Steve Jobs')
        ->and($result->content)->toContain('Apple')
        ->and($result->content)->toContain('https://apple.com');
});

it('image_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'images' => [
            ['title' => 'iPhone 15', 'imageUrl' => 'https://example.com/iphone.jpg', 'sourceUrl' => 'https://example.com', 'sourceName' => 'Example'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/images', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'iphone';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'image_search', 'q' => 'iphone'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('iPhone 15')
        ->and($result->content)->toContain('https://example.com/iphone.jpg');
});

it('news_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'news' => [
            ['title' => 'Tech News', 'source' => 'BBC', 'date' => '2024-01-01', 'link' => 'https://bbc.com/news', 'snippet' => 'Latest news'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/news', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'tech';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'news_search', 'q' => 'tech'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Tech News')
        ->and($result->content)->toContain('BBC')
        ->and($result->content)->toContain('https://bbc.com/news');
});

it('video_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'videos' => [
            ['title' => 'PHP Tutorial', 'source' => 'YouTube', 'date' => '2024-01-01', 'link' => 'https://youtube.com/watch?v=123', 'snippet' => 'Learn PHP'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/videos', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'php';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'video_search', 'q' => 'php'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('PHP Tutorial')
        ->and($result->content)->toContain('YouTube');
});

it('scholar_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'organic' => [
            ['title' => 'Machine Learning Paper', 'link' => 'https://scholar.google.com/paper', 'snippet' => 'Abstract here', 'publicationInfo' => 'Nature 2024'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/scholar', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'machine learning';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'scholar_search', 'q' => 'machine learning'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Machine Learning Paper')
        ->and($result->content)->toContain('Nature 2024');
});

it('shopping_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'shopping' => [
            ['title' => 'MacBook Pro', 'source' => 'Amazon', 'price' => '$1999', 'link' => 'https://amazon.com/macbook', 'snippet' => 'M3 Pro chip'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/shopping', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'macbook';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'shopping_search', 'q' => 'macbook'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('MacBook Pro')
        ->and($result->content)->toContain('$1999')
        ->and($result->content)->toContain('Amazon');
});

it('patents_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'patents' => [
            ['title' => 'Battery Patent', 'patentId' => 'US123456', 'date' => '2024-01-01', 'link' => 'https://patents.google.com/patent', 'snippet' => 'Improved battery tech'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/patents', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'battery';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'patents_search', 'q' => 'battery'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Battery Patent')
        ->and($result->content)->toContain('US123456');
});

it('maps_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'places' => [
            ['title' => 'Coffee Shop', 'address' => '123 Main St', 'phone' => '+1-555-0100', 'rating' => '4.5', 'hours' => '9am-5pm', 'website' => 'https://coffeeshop.com'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/maps', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'coffee';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'maps_search', 'q' => 'coffee'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Coffee Shop')
        ->and($result->content)->toContain('123 Main St')
        ->and($result->content)->toContain('4.5');
});

it('places_search makes correct http request and parses results', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'places' => [
            ['title' => 'Restaurant XYZ', 'address' => '456 Oak Ave', 'phone' => '+1-555-0200', 'rating' => '4.8', 'type' => 'Italian', 'openingHours' => ['Mon-Fri 10am-10pm', 'Sat-Sun 11am-11pm'], 'website' => 'https://restaurant.com', 'url' => 'https://restaurant.com/page'],
        ],
    ]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/places', Mockery::on(function ($options) {
        return $options['headers']['X-API-KEY'] === 'serp_123' && $options['json']['q'] === 'restaurant';
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'places_search', 'q' => 'restaurant'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Restaurant XYZ')
        ->and($result->content)->toContain('456 Oak Ave')
        ->and($result->content)->toContain('Italian');
});

it('returns error for unknown operation', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $client = Mockery::mock(HttpClientInterface::class);
    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'unknown_op', 'q' => 'foo'], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('Unknown operation: unknown_op');
});

it('returns error when http request throws', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $client->allows('request')->andThrow(new RuntimeException('Connection refused'));

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->allows('error');
    $logger->allows('debug');

    $tool = new SerperSearchTool($config, $client, $logger);

    $result = $tool->execute(['action' => 'search', 'q' => 'foo'], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('Search tool error')
        ->and($result->content)->toContain('Connection refused');
});

it('describeAction returns human-readable description for each operation', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $client = Mockery::mock(HttpClientInterface::class);
    $tool = new SerperSearchTool($config, $client);

    expect($tool->describeAction(['action' => 'search', 'q' => 'apple']))->toContain("Search Google via Serper.dev for: 'apple'");
    expect($tool->describeAction(['action' => 'image_search', 'q' => 'cat']))->toContain("Search Google Images for: 'cat'");
    expect($tool->describeAction(['action' => 'unknown_op', 'q' => 'x']))->toContain("Serper search: 'x'");
});

it('uses http_timeout setting when provided', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123', 'http_timeout' => 60]);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn(['organic' => []]);

    $client->expects('request')->with('POST', 'https://google.serper.dev/search', Mockery::on(function ($options) {
        return $options['timeout'] === 60;
    }))->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'search', 'q' => 'apple'], 1);
    expect($result->success)->toBeTrue();
});

it('returns error for empty search query', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $client->shouldNotReceive('request');

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'search', 'q' => ''], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('cannot be empty');
});

it('returns empty-query error for every operation', function (string $action) {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $client->shouldNotReceive('request');

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => $action, 'q' => ''], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('cannot be empty');
})->with([
    'image_search'    => ['image_search'],
    'news_search'     => ['news_search'],
    'video_search'    => ['video_search'],
    'scholar_search'  => ['scholar_search'],
    'shopping_search' => ['shopping_search'],
    'patents_search'  => ['patents_search'],
    'maps_search'     => ['maps_search'],
    'places_search'   => ['places_search'],
]);

it('throws ToolHttpErrorException on 4xx response in search', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(500);
    $response->allows('getContent')->with(false)->andReturn('{"message":"server error"}');

    $client->allows('request')->andReturn($response);

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->allows('error');
    $logger->allows('debug');

    $tool = new SerperSearchTool($config, $client, $logger);

    $result = $tool->execute(['action' => 'search', 'q' => 'apple'], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain('Search tool error')
        ->and($result->content)->toContain('HTTP 500');
});

it('renders answerBox snippet when answer is empty', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'answerBox' => [
            'snippet' => 'A short snippet from answer box',
        ],
        'organic' => [],
    ]);

    $client->allows('request')->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'search', 'q' => 'apple'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Quick Snippet')
        ->and($result->content)->toContain('A short snippet from answer box');
});

it('returns "no results found" branch for every operation', function (string $action, string $endpoint, string $resultKey, string $expectedMessage) {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([$resultKey => []]);

    $client->allows('request')->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => $action, 'q' => 'apple'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain($expectedMessage);
})->with([
    'image_search'    => ['image_search',    'images',   'images',   'No image results found.'],
    'news_search'     => ['news_search',     'news',     'news',     'No news results found.'],
    'video_search'    => ['video_search',    'videos',   'videos',   'No video results found.'],
    'scholar_search'  => ['scholar_search',  'scholar',  'organic',  'No scholar results found.'],
    'shopping_search' => ['shopping_search', 'shopping', 'shopping', 'No shopping results found.'],
    'patents_search'  => ['patents_search',  'patents',  'patents',  'No patent results found.'],
    'maps_search'     => ['maps_search',     'maps',     'places',   'No map results found.'],
    'places_search'   => ['places_search',   'places',   'places',   'No place results found.'],
]);

it('returns operation-specific error prefix when request throws for each operation', function (string $action, string $expectedPrefix) {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $client->allows('request')->andThrow(new RuntimeException('boom'));

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->allows('error');
    $logger->allows('debug');

    $tool = new SerperSearchTool($config, $client, $logger);

    $result = $tool->execute(['action' => $action, 'q' => 'apple'], 1);
    expect($result->success)->toBeFalse()
        ->and($result->content)->toContain($expectedPrefix)
        ->and($result->content)->toContain('boom');
})->with([
    'search'           => ['search',          'Search tool error'],
    'image_search'     => ['image_search',    'Image search error'],
    'news_search'      => ['news_search',     'News search error'],
    'video_search'     => ['video_search',    'Video search error'],
    'scholar_search'   => ['scholar_search',  'Scholar search error'],
    'shopping_search'  => ['shopping_search', 'Shopping search error'],
    'patents_search'   => ['patents_search',  'Patents search error'],
    'maps_search'      => ['maps_search',     'Maps search error'],
    'places_search'    => ['places_search',   'Places search error'],
]);

it('renders places_search URL field when present', function () {
    $config = Mockery::mock(ToolConfigService::class);
    $config->allows('getEffectiveSettings')->with(SerperSearchTool::class, 1, null)->andReturn(['api_key' => 'serp_123']);

    $client = Mockery::mock(HttpClientInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(200);
    $response->allows('toArray')->andReturn([
        'places' => [
            ['title' => 'Restaurant ABC', 'URL' => 'https://restaurant.example/page'],
        ],
    ]);

    $client->allows('request')->andReturn($response);

    $tool = new SerperSearchTool($config, $client);

    $result = $tool->execute(['action' => 'places_search', 'q' => 'restaurant'], 1);
    expect($result->success)->toBeTrue()
        ->and($result->content)->toContain('Restaurant ABC')
        ->and($result->content)->toContain('https://restaurant.example/page');
});
