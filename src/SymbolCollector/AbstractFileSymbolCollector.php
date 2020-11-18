<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\SymbolCollector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

use function property_exists;

abstract class AbstractFileSymbolCollector extends NodeVisitorAbstract implements SymbolCollectorInterface
{
    private string $file;
    /** @var list<string> */
    private array $collected = [];

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @param ClassLike|Function_ $node
     */
    protected static function namespacedName(Node $node): ?string
    {
        if (!property_exists($node, 'namespacedName')) {
            return null;
        }
        return $node->namespacedName->toString();
    }

    /**
     * @return iterable<int, string>
     */
    public function collect(): iterable
    {
        $this->collected = [];
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($this);
        $nodes = (new ParserFactory())->create(ParserFactory::ONLY_PHP7)->parse(\Safe\file_get_contents($this->file));
        if ($nodes === null) {
            return;
        }
        $traverser->traverse($nodes);
        yield from $this->collected;
    }

    /**
     * @return null|int|Node|Node[]
     */
    public function leaveNode(Node $node)
    {
        $symbolName = $this->symbolName($node);
        if ($symbolName !== null) {
            $this->collected[] = $symbolName;
        }
        return null;
    }

    abstract protected function symbolName(Node $node): ?string;
}
