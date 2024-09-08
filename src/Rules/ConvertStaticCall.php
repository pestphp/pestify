<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use Pest\Drift\Parser\NodeFinder\NonTestMethodFinderInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 */
final class ConvertStaticCall extends AbstractConvertStaticCall
{
    /**
     * @var array<int, string>
     */
    private array $classMethodsToConvert = [];

    public function __construct(
        private readonly NonTestMethodFinderInterface $nonTestMethodFinder,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function beforeTraverse(array $nodes)
    {
        $this->classMethodsToConvert = array_map(static fn (ClassMethod $classMethod): string => $classMethod->name->toString(), $this->nonTestMethodFinder->find($nodes));

        return null;
    }

    protected function apply(StaticCall $staticCall): int|Node|array|null
    {
        if (! $staticCall->name instanceof Identifier) {
            return null;
        }
        if (! in_array($staticCall->name->toString(), $this->classMethodsToConvert, true)) {
            return null;
        }

        return new FuncCall(
            new Name($staticCall->name->toString()),
            $staticCall->getArgs(),
            $staticCall->getAttributes()
        );
    }
}
