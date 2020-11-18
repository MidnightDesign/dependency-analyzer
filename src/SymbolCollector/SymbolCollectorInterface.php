<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

interface SymbolCollectorInterface
{
    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable;
}
