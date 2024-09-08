<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use Pest\Drift\Analyzer\ClassMethodAnalyzer;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
abstract class AbstractConvertClassMethod extends NodeVisitorAbstract
{
    public function __construct(
        protected ClassMethodAnalyzer $classMethodAnalyzer,
    ) {}

    /**
     * {@inheritDoc}
     */
    final public function enterNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }
        if (! $this->filter($node)) {
            return null;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    final public function leaveNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }
        if (! $this->filter($node)) {
            return null;
        }

        return $this->apply($node);
    }

    /**
     * @return int|Node|array<Node>|null Replacement node (or special return value)
     */
    abstract protected function apply(ClassMethod $classMethod): int|Node|array|null;

    protected function filter(ClassMethod $classMethod): bool
    {
        return true;
    }
}
