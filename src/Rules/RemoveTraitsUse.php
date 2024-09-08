<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeFinder;

/**
 * @internal
 */
final class RemoveTraitsUse extends AbstractRemoveUse
{
    /**
     * {@inheritDoc}
     */
    protected function useToRemove(array $nodes): array
    {
        $nodeFinder = new NodeFinder;

        /** @var array<int, TraitUse> $traitsUse */
        $traitsUse = $nodeFinder->findInstanceOf($nodes, TraitUse::class);

        $toRemove = [];

        foreach ($traitsUse as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $toRemove[] = $trait->toString();
            }
        }

        return $toRemove;
    }
}
