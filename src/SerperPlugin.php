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

    /**
     * Translation catalogues this plugin ships.
     *
     * Each directory holds one `<lang>.json` per language. Spora resolves
     * a string through **your** catalogue first, then the host's, then the
     * English literal itself, so shipping a catalogue is purely additive:
     * nothing breaks while `de.json` is missing or partial.
     *
     * Keys are the English source text — `de.json` reads
     * `{"Serper Search": "Serper-Suche"}`, not `{"tool": "Serper-Suche"}`.
     * That is what lets the host's CI fail when a string is edited without
     * being re-translated: the key changes, the old entry orphans.
     *
     * **Not everything English belongs in here.** A string the model reads
     * is never translated — not `#[Tool].description`, not
     * `#[ToolParameter].description`, not `#[ToolSetting].label` or its
     * `options`, and not `ToolResult` content. What `locales/de.json`
     * holds is every `#[ToolSetting].description` plus the tool's
     * `displayName`, each a string only an operator ever reads.
     *
     * Requires `spora-core >= 0.31.0` at runtime; older versions fail to
     * resolve the method when the loader is strict.
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
