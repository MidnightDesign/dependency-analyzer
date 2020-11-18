<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use Midnight\DependencyAnalyzer\PackageFileCollector;

final class PackageSymbolCollector implements SymbolCollectorInterface
{
    private string $packageRoot;
    private PackageFileCollector $fileCollector;
    /** @var callable(string): SymbolCollectorInterface */
    private $createFileSymbolCollector;

    public function __construct(string $packageRoot, callable $createFileSymbolCollector)
    {
        $this->packageRoot = $packageRoot;
        $this->fileCollector = new PackageFileCollector($packageRoot);
        $this->createFileSymbolCollector = $createFileSymbolCollector;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        foreach ($this->fileCollector->collect() as $file) {
            yield from ($this->createFileSymbolCollector)($file)->collect();
        }
    }
}
