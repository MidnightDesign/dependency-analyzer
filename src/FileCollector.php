<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FileCollector
{
    /**
     * @return iterable<int, string>
     */
    public static function collect(string $directory): iterable
    {
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
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
