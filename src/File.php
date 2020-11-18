<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use Midnight\DependencyAnalyzer\SymbolCollector\UsedSymbolCollector;

use function array_intersect;
use function count;
use function in_array;

final class File
{
    private string $path;
    /** @var list<string>|null */
    private ?array $usedSymbols = null;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function uses(string $symbol): bool
    {
        return in_array($symbol, $this->usedSymbols(), true);
    }

    public function usesSymbolsFrom(Package $package): bool
    {
        return count(array_intersect($this->usedSymbols(), $package->definedSymbols())) !== 0;
    }

    /**
     * @return list<string>
     */
    private function usedSymbols(): array
    {
        if ($this->usedSymbols === null) {
            $this->usedSymbols = $this->loadUsedSymbols();
        }
        return $this->usedSymbols;
    }

    /**
     * @return list<string>
     */
    private function loadUsedSymbols(): array
    {
        return toArray((new UsedSymbolCollector($this->path))->collect());
    }
}
