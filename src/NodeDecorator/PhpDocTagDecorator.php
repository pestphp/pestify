<?php

declare(strict_types=1);

namespace Pest\Drift\NodeDecorator;

use Pest\Drift\Extractor\PhpDocTagExtractor;
use Pest\Drift\ValueObject\Node\AttributeKey;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

final class PhpDocTagDecorator extends NodeVisitorAbstract
{
    public function __construct(private readonly PhpDocTagExtractor $phpDocTagExtractor) {}

    public function enterNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        $node->setAttribute(AttributeKey::PHP_DOC_TAGS, $this->phpDocTagExtractor->fromComments($node->getComments()));

        return null;
    }
}
