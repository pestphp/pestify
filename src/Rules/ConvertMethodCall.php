<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use Pest\Drift\Parser\NodeFinder\NonTestMethodFinderInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 */
final class ConvertMethodCall extends AbstractConvertMethodCall
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

    protected function apply(MethodCall $methodCall): int|Node|array|null
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }
        if (! in_array($methodCall->name->toString(), $this->classMethodsToConvert, true)) {
            return null;
        }

        return new FuncCall(
            new Name($methodCall->name->toString()),
            $methodCall->getArgs(),
            $methodCall->getAttributes()
        );
    }
}
