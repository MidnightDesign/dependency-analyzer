<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use Midnight\DependencyAnalyzer\SymbolCollector\DefinedSymbolCollector;
use Midnight\DependencyAnalyzer\SymbolCollector\PackageSymbolCollector;
use Midnight\DependencyAnalyzer\SymbolCollector\SymbolCollectorInterface;
use Midnight\DependencyAnalyzer\SymbolCollector\Unique;
use Midnight\DependencyAnalyzer\SymbolCollector\UsedSymbolCollector;

use function count;
use function in_array;

abstract class Package
{
    private string $root;
    /** @var list<string>|null */
    private ?array $definedSymbols = null;
    /** @var list<string>|null */
    private ?array $usedSymbols = null;
    /** @var array{name?: string}|null */
    private ?array $composerJsonContents = null;
    /** @var list<File>|null */
    private ?array $files = null;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function root(): string
    {
        return $this->root;
    }

    public function name(): ?string
    {
        return $this->composerJsonContents()['name'] ?? null;
    }

    /**
     * @return list<string>
     */
    public function definedSymbols(): array
    {
        if ($this->definedSymbols === null) {
            $this->definedSymbols = $this->loadDefinedSymbols();
        }
        return $this->definedSymbols;
    }

    /**
     * @return list<string>
     */
    public function usedSymbols(): array
    {
        if ($this->usedSymbols === null) {
            $this->usedSymbols = $this->loadUsedSymbols();
        }
        return $this->usedSymbols;
    }

    public function defines(string $symbol): bool
    {
        return in_array($symbol, $this->definedSymbols(), true);
    }

    /**
     * @return iterable<int, string>
     */
    public function usedSymbolsDefinedBy(Package $package): iterable
    {
        foreach ($this->usedSymbols() as $symbol) {
            if (!$package->defines($symbol)) {
                continue;
            }
            yield $symbol;
        }
    }

    public function countFilesUsing(string $symbol): int
    {
        $n = 0;
        foreach ($this->files() as $file) {
            if (!$file->uses($symbol)) {
                continue;
            }
            $n++;
        }
        return $n;
    }

    public function countFilesUsingSymbolsFrom(Package $package): int
    {
        $n = 0;
        foreach ($this->files() as $file) {
            if (!$file->usesSymbolsFrom($package)) {
                continue;
            }
            $n++;
        }
        return $n;
    }

    public function countFiles(): int
    {
        return count($this->files());
    }

    /**
     * @return list<string>
     */
    private function loadDefinedSymbols(): array
    {
        $createFileSymbolCollector = fn(string $file): SymbolCollectorInterface => new DefinedSymbolCollector($file);
        return toArray((new PackageSymbolCollector($this->root, $createFileSymbolCollector))->collect());
    }

    /**
     * @return list<string>
     */
    private function loadUsedSymbols(): array
    {
        $createFileSymbolCollector = function (string $file): SymbolCollectorInterface {
            return new UsedSymbolCollector($file);
        };
        $collector = new Unique(new PackageSymbolCollector($this->root, $createFileSymbolCollector));
        return toArray($collector->collect());
    }

    /**
     * @return array{name?: string}
     */
    private function composerJsonContents(): array
    {
        if ($this->composerJsonContents === null) {
            $composerJson = $this->root . '/composer.json';
            $this->composerJsonContents = readComposerJson($composerJson);
        }
        return $this->composerJsonContents;
    }

    /**
     * @return list<File>
     */
    private function files(): array
    {
        if ($this->files === null) {
            $this->files = $this->loadFiles();
        }
        return $this->files;
    }

    /**
     * @return list<File>
     */
    private function loadFiles(): array
    {
        $files = [];
        foreach ((new PackageFileCollector($this->root()))->collect() as $file) {
            $files[] = new File($file);
        }
        return $files;
    }
}
