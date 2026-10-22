<?php

declare(strict_types=1);

namespace Spora\Plugins\Serper;

use Spora\Plugins\AbstractPlugin;
use Spora\Plugins\Serper\Tools\SerperSearchTool;

/** Serper.dev Google search results for Spora agents. */
final class SerperPlugin extends AbstractPlugin
{
    public function getName(): string
    {
        return 'Serper';
    }

    /** @return array<class-string<\Spora\Tools\ToolInterface>> */
    public function tools(): array
    {
        return [SerperSearchTool::class];
    }

    /**
     * Absolute path to the plugin's bundled `skills/` directory.
     *
     * `SkillScanner` reads immediate subdirectories only, each needing a
     * `SKILL.md`; a skill nested deeper is never seen.
     *
     * @return string[]
     */
    public function skillPaths(): array
    {
        $path = \dirname(__DIR__) . '/skills';
        return is_dir($path) ? [$path] : [];
    }

    /**
     * Absolute path to the plugin's `locales/` directory, holding one
     * `<locale>.json` per language.
     *
     * @return string[]
     */
    public function translationsPaths(): array
    {
        return [
            __DIR__ . '/../locales',
        ];
    }
}
