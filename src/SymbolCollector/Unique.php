<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use function in_array;

final class Unique implements SymbolCollectorInterface
{
    private SymbolCollectorInterface $collector;

    public function __construct(SymbolCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        $seen = [];
        foreach ($this->collector->collect() as $item) {
            if (in_array($item, $seen, true)) {
                continue;
            }
            yield $item;
            $seen[] = $item;
        }
    }
}
