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
}
