<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use PhpParser\Node;

final class UsedSymbolCollector extends AbstractFileSymbolCollector
{
    protected function symbolName(Node $node): ?string
    {
        if ($node instanceof Node\Name\FullyQualified) {
            return $node->toString();
        }
        if ($node instanceof Node\Stmt\UseUse) {
            return $node->name->toString();
        }
        return null;
    }
}
