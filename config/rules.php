<?php

declare(strict_types=1);

$classMethodAnalyzer = new \Pest\Drift\Analyzer\ClassMethodAnalyzer;
$nodeFinder = new \PhpParser\NodeFinder;
$phpDocTagExtractor = new \Pest\Drift\Extractor\PhpDocTagExtractor;

return [
    new \Pest\Drift\NodeDecorator\PhpDocTagDecorator($phpDocTagExtractor),
    new \Pest\Drift\NodeDecorator\DataProviderDecorator($nodeFinder, $phpDocTagExtractor, $classMethodAnalyzer),
    new \Pest\Drift\Rules\RemoveClass,
    new \Pest\Drift\Rules\RemoveNamespace,
    new \Pest\Drift\Rules\ExtendsToUses,
    new \Pest\Drift\Rules\RemoveExtendsUse,
    new \Pest\Drift\Rules\RemoveTraitsUse,
    new \Pest\Drift\Rules\ConvertTestMethod($classMethodAnalyzer, [
        new \Pest\Drift\Rules\AttributeAnnotations\ConvertDataProvider,
        new \Pest\Drift\Rules\AttributeAnnotations\ConvertDepends,
        new \Pest\Drift\Rules\AttributeAnnotations\ConvertGroup,
    ]),
    new \Pest\Drift\Rules\ConvertNonTestMethod($classMethodAnalyzer),
    new \Pest\Drift\Rules\ConvertMethodCall(
        new \Pest\Drift\Parser\NodeFinder\NonTestMethodFinder(
            new \Pest\Drift\Parser\NodeFinder\ClassMethodFinder($nodeFinder),
            $classMethodAnalyzer
        )
    ),
    new \Pest\Drift\Rules\ConvertStaticCall(
        new \Pest\Drift\Parser\NodeFinder\NonTestMethodFinder(
            new \Pest\Drift\Parser\NodeFinder\ClassMethodFinder($nodeFinder),
            $classMethodAnalyzer
        )
    ),
    new \Pest\Drift\Rules\SetUpToBeforeEach($classMethodAnalyzer),
    new \Pest\Drift\Rules\SetUpBeforeClassToBeforeAll($classMethodAnalyzer),
    new \Pest\Drift\Rules\TearDownToAfterEach($classMethodAnalyzer),
    new \Pest\Drift\Rules\TearDownAfterClassToAfterAll($classMethodAnalyzer),
    new \Pest\Drift\Rules\RemoveProperties,
    new \Pest\Drift\Rules\TraitToUses,
    new \Pest\Drift\Rules\AddMissingUse(
        new \Pest\Drift\Parser\NodeFinder\MissingUseFinder(
            new \Pest\Drift\Parser\NodeFinder\UseFinder($nodeFinder),
            new \Pest\Drift\Parser\NodeFinder\NameFinder($nodeFinder),
        )
    ),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertEquals', 'toEqual', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertInstanceOf', 'toBeInstanceOf', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertTrue', 'toBeTrue', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertFalse', 'toBeFalse', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsArray', 'toBeArray', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertArrayHasKey', 'toHaveKey', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsString', 'toBeString', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertEmpty', 'toBeEmpty', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToNegativeExpectation('assertNotEmpty', 'toBeEmpty', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertContains', 'toContain', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToNegativeExpectation('assertNotContains', 'toContain', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertSame', 'toBe', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertNull', 'toBeNull', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToNegativeExpectation('assertNotNull', 'toBeNull', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertStringStartsWith', 'toStartWith', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertStringEndsWith', 'toEndWith', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertThat', 'toMatchConstraint', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertMatchesRegularExpression', 'toMatch', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertFileExists', 'toBeFile', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertFileIsReadable', 'toBeReadableFile', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertFileIsWritable', 'toBeWritableFile', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertDirectoryExists', 'toBeDirectory', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertDirectoryIsReadable', 'toBeReadableDirectory', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertDirectoryIsWritable', 'toBeWritableDirectory', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertNan', 'toBeNan', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertJson', 'toBeJson', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsScalar', 'toBeScalar', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsResource', 'toBeResource', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsObject', 'toBeObject', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsNumeric', 'toBeNumeric', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsIterable', 'toBeIterable', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsInt', 'toBeInt', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsFloat', 'toBeFloat', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsCallable', 'toBeCallable', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertIsBool', 'toBeBool', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertInfinite', 'toBeInfinite', 2),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertEqualsWithDelta', 'toEqualWithDelta', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertEqualsCanonicalizing', 'toEqualCanonicalizing', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertCount', 'toHaveCount', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertLessThan', 'toBeLessThan', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertLessThanOrEqual', 'toBeLessThanOrEqual', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertGreaterThan', 'toBeGreaterThan', 3),
    new \Pest\Drift\Rules\Assertions\AssertionToExpectation('assertGreaterThanOrEqual', 'toBeGreaterThanOrEqual', 3),
];
