<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use Midnight\DependencyAnalyzer\FileCollector;

final class DirectorySymbolCollector implements SymbolCollectorInterface
{
    private string $directory;
    /** @var callable(string $file): SymbolCollectorInterface */
    private $createFileSymbolCollector;

    public function __construct(string $directory, callable $createFileSymbolCollector)
    {
        $this->directory = $directory;
        $this->createFileSymbolCollector = $createFileSymbolCollector;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        foreach (FileCollector::collect($this->directory) as $file) {
            foreach (($this->createFileSymbolCollector)($file)->collect() as $symbol) {
                yield $symbol;
            }
        }
    }
}
