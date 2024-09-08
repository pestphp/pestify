<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;

/**
 * @internal
 */
final class RemoveExtendsUse extends AbstractRemoveUse
{
    /**
     * {@inheritDoc}
     */
    protected function useToRemove(array $nodes): array
    {
        $nodeFinder = new NodeFinder;

        /** @var array<int, Class_> $classesWithExtends */
        $classesWithExtends = $nodeFinder->findInstanceOf($nodes, Class_::class);

        $toRemove = [];

        foreach ($classesWithExtends as $classWithExtends) {
            if (! $classWithExtends->extends instanceof \PhpParser\Node\Name) {
                continue;
            }
            $toRemove[] = $classWithExtends->extends->toString();
        }

        return $toRemove;
    }
}
