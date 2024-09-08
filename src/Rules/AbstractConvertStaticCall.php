<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
abstract class AbstractConvertStaticCall extends NodeVisitorAbstract
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
        if ($node instanceof StaticCall) {
            return $this->apply($node);
        }

        return null;
    }

    /**
     * @return int|Node|array<Node>|null Replacement node (or special return value)
     */
    abstract protected function apply(StaticCall $staticCall): int|Node|array|null;
}
