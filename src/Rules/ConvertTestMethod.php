<?php

declare(strict_types=1);

namespace Pest\Drift\Rules;

use Pest\Drift\Analyzer\ClassMethodAnalyzer;
use Pest\Drift\Rules\AttributeAnnotations\AbstractConvertAttributeAnnotation;
use Pest\Drift\ValueObject\Node\AttributeKey;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;

/**
 * @internal
 */
final class ConvertTestMethod extends AbstractConvertClassMethod
{
    /**
     * @param  AbstractConvertAttributeAnnotation[]  $attributeAnnotationConverters
     */
    public function __construct(ClassMethodAnalyzer $classMethodAnalyzer, private readonly array $attributeAnnotationConverters)
    {
        parent::__construct($classMethodAnalyzer);
    }

    /**
     * {@inheritDoc}
     */
    protected function apply(ClassMethod $classMethod): int|Node|array|null
    {
        $methodName = $classMethod->name->toString();
        $attributes = $classMethod->getAttributes();
        $comments = $classMethod->getComments();
        /** @var array<string, array<int, string>> $phpDocTags */
        $phpDocTags = $classMethod->getAttribute(AttributeKey::PHP_DOC_TAGS, []);
        $attributeGroups = $this->classMethodAnalyzer->reduceAttrGroups($classMethod);

        if ($comments !== []) {
            $attributes['comments'] = [];
        }

        // Build test function.
        $testCall = new FuncCall(
            new Name($this->guessFunctionCall($methodName)),
            [
                new Arg(new String_($this->methodNameToDescription($methodName))),
                new Arg(new Closure([
                    'stmts' => $classMethod->stmts ?? [],
                    'params' => $classMethod->getParams(),
                ])),
            ]
        );

        foreach ($this->attributeAnnotationConverters as $attributeAnnotationConverter) {
            $testCall = $attributeAnnotationConverter->apply($testCall, $phpDocTags, $attributeGroups);
        }

        return new Expression($testCall, $attributes);
    }

    /**
     * Filter class methods to convert.
     */
    protected function filter(ClassMethod $classMethod): bool
    {
        return $this->classMethodAnalyzer->isTestMethod($classMethod);
    }

    /**
     * Extract Pest description from method name.
     */
    private function methodNameToDescription(string $name): string
    {
        $newName = (string) preg_replace(
            ['/^(test|it)/', '/(?<![A-Z]|_)(?=[A-Z])/', '/_(?<![A-Z])/'],
            ['', ' ', ' '],
            $name
        );

        return trim(strtolower($newName));
    }

    /**
     * Guess if the test function call must be 'test' or 'it'.
     */
    private function guessFunctionCall(string $methodName): string
    {
        if (str_starts_with($methodName, 'it')) {
            return 'it';
        }

        return 'test';
    }
}
