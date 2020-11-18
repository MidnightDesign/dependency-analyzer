<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

final class Project extends Package
{
    /** @var iterable<int, Dependency>|null */
    private ?iterable $dependencies = null;

    /**
     * @return iterable<int, Dependency>
     */
    public function dependencies(): iterable
    {
        if ($this->dependencies === null) {
            $this->dependencies = $this->loadDependencies();
        }
        yield from $this->dependencies;
    }

    /**
     * @return iterable<int, string>
     */
    public function usedExternalSymbols(): iterable
    {
        foreach ($this->usedSymbols() as $symbol) {
            if ($this->defines($symbol)) {
                continue;
            }
            yield $symbol;
        }
    }

    /**
     * @return iterable<int, Dependency>
     */
    private function loadDependencies(): iterable
    {
        $root = $this->root();
        foreach ((new DependencyCollector($root . '/composer.json'))->collect() as $packageName) {
            yield new Dependency($root . '/vendor/' . $packageName);
        }
    }
}
