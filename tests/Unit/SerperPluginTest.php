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

it('exposes the bundled skills/ directory', function () {
    $plugin = new SerperPlugin();
    $paths = $plugin->skillPaths();

    expect($paths)->toBeArray()
        ->and($paths)->toHaveCount(1)
        ->and($paths[0])->toEndWith('/skills')
        ->and($paths[0])->toBeDirectory();

    // The scanner looks depth-1 for SKILL.md; verify our bundled skill is
    // actually there so a wiring regression is caught by tests, not just
    // at runtime in a Spora install.
    expect($paths[0] . '/serper-search/SKILL.md')->toBeFile();
});
