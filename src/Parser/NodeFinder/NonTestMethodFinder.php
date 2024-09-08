<?php

declare(strict_types=1);

namespace Pest\Drift\Parser\NodeFinder;

use Pest\Drift\Analyzer\ClassMethodAnalyzerInterface;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 */
final class NonTestMethodFinder implements NonTestMethodFinderInterface
{
    public function __construct(
        private readonly ClassMethodFinderInterface $classMethodFinder,
        private readonly ClassMethodAnalyzerInterface $classMethodAnalyzer,
    ) {}

    public function find(array $nodes): array
    {
        $classMethods = $this->classMethodFinder->find($nodes);

        return array_filter($classMethods, fn (ClassMethod $classMethod): bool => ! $this->classMethodAnalyzer->isLifecycleMethod($classMethod) && ! $this->classMethodAnalyzer->isTestMethod($classMethod));
    }
}
