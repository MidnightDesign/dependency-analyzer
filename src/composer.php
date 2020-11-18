<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

/**
 * @return array{
 *     name?: string,
 *     require?: array<string, string>,
 * }
 */
function readComposerJson(string $file): array
{
    return \Safe\json_decode(\Safe\file_get_contents($file), true);
}
