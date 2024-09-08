<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class TraitToUses extends NodeVisitorAbstract
{
    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node)
    {
        if (! $node instanceof TraitUse) {
            return null;
        }

        $usesStatements = array_map(static fn (Name $trait): Expression => new Expression(
            new FuncCall(
                new Name('uses'),
                [
                    new Arg(new ClassConstFetch($trait->getAttribute('resolvedName'), 'class')), // @phpstan-ignore-line
                ]
            )
        ), $node->traits);

        return array_merge(
            $usesStatements,
            [new Node\Stmt\Nop]
        );
    }
}
