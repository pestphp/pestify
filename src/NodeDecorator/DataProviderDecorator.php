<?php

declare(strict_types=1);

namespace Pest\Drift\NodeDecorator;

use Pest\Drift\Analyzer\ClassMethodAnalyzer;
use Pest\Drift\Extractor\PhpDocTagExtractor;
use Pest\Drift\ValueObject\Node\AttributeKey;
use Pest\Drift\ValueObject\PhpUnit\TagKey;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

final class DataProviderDecorator extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private array $dataProviders = [];

    public function __construct(private readonly NodeFinder $nodeFinder, private readonly PhpDocTagExtractor $phpDocTagExtractor, private readonly ClassMethodAnalyzer $classMethodAnalyzer) {}

    public function beforeTraverse(array $nodes)
    {
        /** @var array<int, ClassMethod>  $classMethods */
        $classMethods = $this->nodeFinder->findInstanceOf($nodes, ClassMethod::class);

        foreach ($classMethods as $classMethod) {
            $phpDocTags = $this->phpDocTagExtractor->fromComments($classMethod->getComments());
            $attributeGroups = $this->classMethodAnalyzer->reduceAttrGroups($classMethod);
            $dataProviders = $attributeGroups[\Pest\Drift\ValueObject\PhpUnit\AttributeKey::DATA_PROVIDER] ??
                            $attributeGroups[\Pest\Drift\ValueObject\PhpUnit\AttributeKey::DATA_PROVIDER_EXTERNAL] ??
                            $phpDocTags[TagKey::DATA_PROVIDER] ??
                            $phpDocTags[TagKey::DATA_PROVIDER_EXTERNAL] ??
                            [];

            $this->dataProviders = [...$this->dataProviders, ...$dataProviders];
        }

        return parent::beforeTraverse($nodes);
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        $node->setAttribute(AttributeKey::IS_DATA_PROVIDER, in_array($node->name->toString(), $this->dataProviders, true));

        return null;
    }
}
