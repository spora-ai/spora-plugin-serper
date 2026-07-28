<?php

declare(strict_types=1);

namespace Spora\Plugins\Serper;

use Spora\Plugins\AbstractPlugin;
use Spora\Plugins\Serper\Tools\SerperSearchTool;

/**
 * Serper.dev Google search results for Spora agents.
 */
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
     * Absolute path to the plugin's bundled `skills/` directory. Spora's
     * SkillScanner walks each returned path depth-1 and treats immediate
     * subdirectories as skill roots (each must contain a SKILL.md).
     *
     * Adding a new skill is two steps: drop `skills/<name>/SKILL.md` and
     * re-publish — no entry-point changes needed unless the directory
     * itself moves.
     *
     * @return string[]
     */
    public function skillPaths(): array
    {
        $path = \dirname(__DIR__) . '/skills';
        return is_dir($path) ? [$path] : [];
    }
}
