<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;

final class FileCollector
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->directory);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $file = $file->getRealPath();
            if ($file === false) {
                continue;
            }
            yield $file;
        }
    }
}
