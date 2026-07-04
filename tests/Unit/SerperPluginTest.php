<?php

declare(strict_types=1);

use Spora\Plugins\Serper\SerperPlugin;
use Spora\Plugins\Serper\Tools\SerperSearchTool;

it('returns plugin name', function () {
    $plugin = new SerperPlugin();
    expect($plugin->getName())->toBe('Serper');
});

it('contributes the SerperSearchTool', function () {
    $plugin = new SerperPlugin();
    expect($plugin->tools())->toBe([SerperSearchTool::class]);
});
