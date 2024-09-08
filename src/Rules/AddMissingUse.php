<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use Pest\Drift\Parser\NodeFinder\MissingUseFinderInterface;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class AddMissingUse extends NodeVisitorAbstract
{
    public function __construct(
        private readonly MissingUseFinderInterface $missingUseFinder,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function afterTraverse(array $nodes)
    {
        $missingUses = $this->missingUseFinder->find($nodes);

        // Add missing uses.
        foreach ($missingUses as $missingUse) {
            $resolvedName = $missingUse->getAttribute('resolvedName');
            assert($resolvedName instanceof Name);

            $use = new Use_([
                new UseItem($resolvedName),
            ]);

            array_splice($nodes, 1, 0, [$use]);
        }

        return $nodes;
    }
}
