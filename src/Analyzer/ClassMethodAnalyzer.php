<?php

declare(strict_types=1);

namespace Pest\Drift\Analyzer;

use Pest\Drift\ValueObject\Node\AttributeKey;
use Pest\Drift\ValueObject\PhpUnit\TagKey;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 */
final class ClassMethodAnalyzer implements ClassMethodAnalyzerInterface
{
    /**
     * Check if the class method is a lifecycle method.
     */
    public function isLifecycleMethod(ClassMethod $classMethod): bool
    {
        return in_array($classMethod->name->toString(), [
            'setUp',
            'setUpBeforeClass',
            'tearDown',
            'tearDownAfterClass',
        ], true);
    }

    /**
     * Check if the class method is a test method.
     */
    public function isTestMethod(ClassMethod $classMethod): bool
    {
        if (str_starts_with($classMethod->name->toString(), 'test')) {
            return true;
        }
        if ($this->containsTestAnnotation($classMethod)) {
            return true;
        }

        return $this->containsTestAttribute($classMethod);
    }

    /**
     * Search @test annotations in comments.
     */
    private function containsTestAnnotation(ClassMethod $classMethod): bool
    {
        /** @var array<string, array<int, string>> $phpDocTags */
        $phpDocTags = $classMethod->getAttribute(AttributeKey::PHP_DOC_TAGS, []);

        return array_key_exists(TagKey::TEST, $phpDocTags);
    }

    /**
     * Search #[Test] attribute.
     */
    private function containsTestAttribute(ClassMethod $classMethod): bool
    {
        return array_key_exists(\Pest\Drift\ValueObject\PhpUnit\AttributeKey::TEST, $this->reduceAttrGroups($classMethod));
    }

    /**
     * Reduce classMethod attrGroups and return an array with attrs names.
     *
     * @return string[][]
     */
    public function reduceAttrGroups(ClassMethod $classMethod): array
    {
        $attributeNames = array_map(fn ($attrGroup): array => $this->getAttributesValues($attrGroup->attrs), $classMethod->getAttrGroups());

        // Flatten the array
        return array_reduce($attributeNames, fn ($array, $item): array => array_merge($array, $item), []);
    }

    /**
     * Get attribute names from the group.
     *
     * @param  \PhpParser\Node\Attribute[]  $attributes
     * @return array<string, string[]>
     */
    private function getAttributesValues(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attr) {
            $result[$attr->name->toString()] = $this->getValuesFromArguments($attr->args);
        }

        return $result;
    }

    /**
     * @param  \PhpParser\Node\Arg[]  $arguments
     * @return string[]
     */
    private function getValuesFromArguments(array $arguments): array
    {
        return array_reduce($arguments, function ($values, $argument) {
            if ($argument->value instanceof Scalar\String_) {
                $values[] = $argument->value->value; // @phpstan-ignore-line
            }

            return $values;
        }, []);
    }
}
