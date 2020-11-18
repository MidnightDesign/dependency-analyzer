<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use function is_string;

final class PackageFileCollector
{
    private string $packageRoot;

    public function __construct(string $packageRoot)
    {
        $this->packageRoot = $packageRoot;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        $composerJson = $this->packageRoot . '/composer.json';
        $contents = \Safe\json_decode(\Safe\file_get_contents($composerJson), true);
        $autoload = $contents['autoload'] ?? [];
        foreach ($autoload['psr-4'] ?? [] as $directories) {
            $directories = is_string($directories) ? [$directories] : $directories;
            foreach ($directories as $directory) {
                yield from FileCollector::collect($this->packageRoot . '/' . $directory);
            }
        }
        foreach ($autoload['files'] ?? [] as $file) {
            yield $this->packageRoot . '/' . $file;
        }
    }
}
