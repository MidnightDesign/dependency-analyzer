<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer;

use Traversable;

use function iterator_to_array;

/**
 * @template T
 * @param iterable<array-key, T> $items
 * @return list<T>
 */
function toArray(iterable $items): array
{
    return $items instanceof Traversable ? iterator_to_array($items, false) : $items;
}
