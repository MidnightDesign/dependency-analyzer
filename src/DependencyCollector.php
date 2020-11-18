<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use function array_keys;
use function str_contains;

final class DependencyCollector
{
    private string $composerJson;

    public function __construct(string $composerJson)
    {
        $this->composerJson = $composerJson;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        $contents = readComposerJson($this->composerJson);
        foreach (array_keys($contents['require'] ?? []) as $packageName) {
            if (!str_contains($packageName, '/')) {
                continue;
            }
            yield $packageName;
        }
    }
}
