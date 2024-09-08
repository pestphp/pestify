<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
abstract class AbstractConvertMethodCall extends NodeVisitorAbstract
{
    /**
     * {@inheritDoc}
     */
    final public function enterNode(Node $node): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    final public function leaveNode(Node $node)
    {
        if ($node instanceof MethodCall) {
            return $this->apply($node);
        }

        return null;
    }

    /**
     * @return int|Node|array<Node>|null Replacement node (or special return value)
     */
    abstract protected function apply(MethodCall $methodCall): int|Node|array|null;
}
