<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use PhpParser\Node;

final class DefinedSymbolCollector extends AbstractFileSymbolCollector
{
    protected function symbolName(Node $node): ?string
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            return self::namespacedName($node);
        }
        if ($node instanceof Node\Stmt\Function_) {
            return self::namespacedName($node);
        }
        return null;
    }
}
