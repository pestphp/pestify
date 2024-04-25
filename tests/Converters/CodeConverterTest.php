<?php

use Pest\Drift\Converters\CodeConverterFactory;

function codeConverter()
{
    return (new CodeConverterFactory())->codeConverter();
}

it('remove namespace', function () {
    $code = '<?php namespace Test\Namespace;';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain('namespace');
});

it('remove class', function () {
    $code = '<?php class MyTest {}';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain('class FooTest');
});

it('keep anonymous class', function () {
    $code = <<<'CODE'
<?php
class MyTest {
    public function test_foo(): void
    {
        $step = new class
        {
            public function testFunction(): array
            {
                return [];
            }
        };
        $result = (new ExampleClass())->exampleMethod([$step]);
        $this->assertTrue($result);
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php
test('foo', function () {
    $step = new class
    {
        test('function', function () {
            return [];
        });
    };
    $result = (new ExampleClass())->exampleMethod([$step]);
    expect($result)->toBeTrue();
});
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('remove unnecessary use', function () {
    $code = '<?php
        use PHPUnit\Framework\TestCase;
        use My\Class;

        class MyTest extends TestCase {}
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain("use PHPUnit\Framework\TestCase;");
    expect($convertedCode)->toContain("use My\Class;");
});

it('convert extends class to uses method', function () {
    $code = '<?php
        use Pest\Drift\Tests\Fixtures\FixtureTestCase;

        class MyTest extends FixtureTestCase {}
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->not->toContain('use Pest\Drift\Tests\Fixtures\FixtureTestCase;')
        ->toContain("uses(\Pest\Drift\Tests\Fixtures\FixtureTestCase::class);");
});

it('doesnt convert extends PhpUnit TestCase', function () {
    $code = '<?php
        use PHPUnit\Framework\TestCase;

        class MyTest extends TestCase {}
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain("uses(\PHPUnit\Framework\TestCase::class);");
    expect($convertedCode)->not->toContain('uses(TestCase::class);');
});

it('convert phpunit class method to pest function call', function () {
    $code = '<?php
        class MyTest {
            public function test_true_is_true() {}

            public function testFalseIsFalse() {}

            public function testPHP8() {}

            public function testPHP_IS_Great() {}

            /** @test */
            public function it_works() {}
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain("test('true is true', function () {")
        ->toContain("test('false is false', function () {")
        ->toContain("test('php8', function () {")
        ->toContain("test('php is great', function () {")
        ->toContain("it('works', function () {")
        ->not->toContain('/** @test */');
});

it('convert phpunit class method to pest function call from attribute', function () {
    $code = '<?php
        class MyTest {
            #[Test]
            public function it_does_something(): void
            {
                // ...
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain("it('does something', function () {")
        ->not->toContain('#[Test]');
});

it('convert lifecyle method', function () {
    $code = '<?php
        class MyTest {
            protected function setUp() {
                parent::setUp();
            }
            protected function setUpBeforeClass() {
                parent::setUpBeforeClass();
            }
            protected function tearDown() {
                parent::tearDown();
            }
            protected function tearDownAfterClass() {
                parent::tearDownAfterClass();
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('beforeEach')
        ->and($convertedCode)->toContain('beforeAll')
        ->and($convertedCode)->toContain('afterEach')
        ->and($convertedCode)->toContain('afterAll')
        ->and($convertedCode)->not->toContain('setUp')
        ->and($convertedCode)->not->toContain('setUpBeforeClass')
        ->and($convertedCode)->not->toContain('tearDown')
        ->and($convertedCode)->not->toContain('tearDownAfterClass');
});

it('convert non test method', function () {
    $code = '<?php
        class MyTest {
            protected function thisIsNotATest() {}

            public function test_non_test_method()
            {
                $this->thisIsNotATest();
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('function thisIsNotATest()')
        ->not->toContain('protected function thisIsNotATest()')
        ->toContain('thisIsNotATest();')
        ->not->toContain('$this->thisIsNotATest()');
});

it('convert non test static method calls', function () {
    $code = '<?php
        class MyTest {
            protected static function thisIsNotATest() {}

            public function test_non_test_method()
            {
                self::thisIsNotATest();
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('function thisIsNotATest()')
        ->not->toContain('protected static function thisIsNotATest()')
        ->toContain('thisIsNotATest();')
        ->not->toContain('self::thisIsNotATest()');
});

it('remove properties', function () {
    $code = '<?php
        class MyTest {
            protected $myProperty;
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain('protected $myProperty;');
});

it('keep using traits', function () {
    $code = '<?php
        use My\CustomTrait;
        class MyTest {
            use CustomTrait;
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('uses(\My\CustomTrait::class);')
        ->not->toContain('use My\CustomTrait;')
        ->not->toContain('use CustomTrait;');
});

it('add missing use', function () {
    $code = '<?php
        namespace Pest\Drift\Tests;

        use Pest\Drift\Tests\Helper\Foo;

        class MyTest {
            public function test_foo()
            {
                $foo = new Foo();
                $bar = new Bar();
                $date = new DateTime();
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('use \Pest\Drift\Tests\Bar;');
});

it('keep multiline statements', function () {
    $code = <<<'CODE'
<?php
class MyTest {
    public function multiline_statement()
    {
        $object
            ->foo()
            ->bar()
            ->hello(
                $the,
                $world
            );

        $alpha = "beta";
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php
function multiline_statement()
{
    $object
        ->foo()
        ->bar()
        ->hello(
            $the,
            $world
        );

    $alpha = "beta";
}
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('keep breakline between methods', function () {
    $code = '<?php
        class MyTest {
            public function test_method()
            {
            }

            public function first_method()
            {
            }

            public function second_method()
            {
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    $expected = "
test('method', function () {
});

function first_method()
{
}

function second_method()
{
}
";

    expect($convertedCode)->toContain($expected);
});

it('does not duplicate break line', function () {
    $code = '<?php
test("the application returns a successful response", function () {
    $response = $this->get("/");

    $response->assertStatus(200);
});
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toEqual($code);
});

it('add break line after uses statements', function () {
    $code = <<<'CODE'
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CustomTestCase;

class ExampleTest extends CustomTestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php

uses(\Tests\CustomTestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
});
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('keep break line after use statements', function () {
    $code = <<<'CODE'
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
});
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('convert data providers to dataset', function () {
    $code = '<?php

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * @dataProvider emailProvider
     */
    public function testHasEmails(string $email)
    {
    }

    public function emailProvider()
    {
        return ["example@example.com", "other@example.com"];
    }
}
';

    $convertedCode = codeConverter()->convert($code);

    $expected = '<?php

test(\'has emails\', function (string $email) {
})->with(\'emailProvider\');

dataset(\'emailProvider\', function () {
    return ["example@example.com", "other@example.com"];
});
';

    expect($convertedCode)->toEqual($expected);
});

it('convert external data providers to dataset', function (string $attribute) {
    $code = <<<CODE
<?php

use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class ExampleTest extends TestCase
{
    $attribute
    public function testHasEmails(string \$email)
    {
    }
}

final class ExternalProviders {
    public static function emailProvider()
    {
        return ["example@example.com", "other@example.com"];
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain("->with('emailProvider');")->toContain("dataset('emailProvider', function");
})->with([
    'DataProviderExternal Attribute' => "#[DataProviderExternal(ExternalProviders::class, 'emailProvider')]",
    'phpDoc Tag' => "/**\n    * @dataProviderExternal emailProvider\n    */",
]);

it('convert data providers to dataset from attribute', function () {
    $code = '<?php

use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[DataProvider(\'emailProvider\')]
    public function testHasEmails(string $email)
    {
    }

    public function emailProvider()
    {
        return ["example@example.com", "other@example.com"];
    }
}
';

    $convertedCode = codeConverter()->convert($code);

    $expected = '<?php

test(\'has emails\', function (string $email) {
})->with(\'emailProvider\');

dataset(\'emailProvider\', function () {
    return ["example@example.com", "other@example.com"];
});
';

    expect($convertedCode)->toEqual($expected);
});

it('remove annotations', function () {
    $code = <<<'CODE'
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php

test('the application returns a successful response', function () {
});
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('convert assertEquals to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_equals()
            {
                $this->assertEquals("foo", "bar");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("bar")->toEqual("foo")');
});

it('convert assertInstanceOf to Pest expectation', function () {
    $code = '<?php
        use Pest\Drift\Tests\Fixtures\Some\Thing;
        class MyTest {
            public function test_instanceof()
            {
                $this->assertInstanceOf(Thing::class, $thing);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('use Pest\Drift\Tests\Fixtures\Some\Thing;')
        ->toContain('expect($thing)->toBeInstanceOf(Thing::class)');
});

it('convert assertTrue to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_true()
            {
                $this->assertTrue(true);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(true)->toBeTrue()');
});

it('convert assertIsArray to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_array()
            {
                $this->assertIsArray([]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->toBeArray()');
});

it('convert assertArrayHasKey to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_array_has_key()
            {
                $this->assertArrayHasKey("foo", ["foo"]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(["foo"])->toHaveKey("foo")');
});

it('convert assertIsString to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_string()
            {
                $this->assertIsString("foo");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("foo")->toBeString()');
});

it('convert assertEmpty to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_empty()
            {
                $this->assertEmpty([]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->toBeEmpty()');
});

it('convert assertNotEmpty to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_not_empty()
            {
                $this->assertNotEmpty([]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->not->toBeEmpty()');
});

it('convert assertContains to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_contains()
            {
                $this->assertContains(1, []);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->toContain(1)');
});

it('convert assertNotContains to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_not_contains()
            {
                $this->assertNotContains(1, []);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->not->toContain(1)');
});

it('convert assertSame to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_same()
            {
                $myObject = new Object();

                $this->assertSame($object, $object);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect($object)->toBe($object)');
});

it('convert assertNull to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_null()
            {
                $this->assertNull(null);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(null)->toBeNull()');
});

it('convert assertNotNull to Pest expectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_null()
            {
                $this->assertNotNull(null);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(null)->not->toBeNull()');
});

it('convert assertFalse to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_false()
            {
                $this->assertFalse(false);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(false)->toBeFalse()');
});

it('convert assertGreaterThan to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_greater_than()
            {
                $this->assertGreaterThan(10, 20);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(20)->toBeGreaterThan(10)');
});

it('convert assertGreaterThanOrEqual to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_greater_than_or_equal()
            {
                $this->assertGreaterThanOrEqual(10, 20);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(20)->toBeGreaterThanOrEqual(10)');
});

it('convert assertLessThan to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_less_than()
            {
                $this->assertLessThan(10, 20);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(20)->toBeLessThan(10)');
});

it('convert assertLessThanOrEqual to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_less_than_or_equal()
            {
                $this->assertLessThanOrEqual(10, 20);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(20)->toBeLessThanOrEqual(10)');
});

it('convert assertCount to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_count()
            {
                $this->assertCount(10, []);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([])->toHaveCount(10)');
});

it('convert assertEqualsCanonicalizing to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_equals_canonicalizing()
            {
                $this->assertEqualsCanonicalizing([1, 2], [2, 1]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([2, 1])->toEqualCanonicalizing([1, 2])');
});

it('convert assertEqualsWithDelta to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_equals_with_delta()
            {
                $this->assertEqualsWithDelta(10, 14, 5);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(14)->toEqualWithDelta(10, 5)');
});

it('convert assertInfinite to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_infinite()
            {
                $this->assertInfinite(10);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(10)->toBeInfinite()');
});

it('convert assertIsBool to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_bool()
            {
                $this->assertIsBool(true);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(true)->toBeBool()');
});

it('convert assertIsCallable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_callable()
            {
                $this->assertIsCallable(true);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(true)->toBeCallable()');
});

it('convert assertIsFloat to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_float()
            {
                $this->assertIsFloat(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeFloat()');
});

it('convert assertIsInt to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_int()
            {
                $this->assertIsInt(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeInt()');
});

it('convert assertIsIterable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_iterable()
            {
                $this->assertIsIterable([1]);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect([1])->toBeIterable()');
});

it('convert assertIsNumeric to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_numeric()
            {
                $this->assertIsNumeric(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeNumeric()');
});

it('convert assertIsObject to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_object()
            {
                $this->assertIsObject(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeObject()');
});

it('convert assertIsResource to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_resource()
            {
                $this->assertIsResource(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeResource()');
});

it('convert assertIsScalar to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_is_scalar()
            {
                $this->assertIsScalar(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeScalar()');
});

it('convert assertJson to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_json()
            {
                $this->assertJson(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeJson()');
});

it('do not convert assertJson when not called with this', function () {
    $code = <<<'CODE'
<?php
class MyTest {
    public function test_non_phpunit_assert_json()
    {
        $response->assertJson([
            "data" => [
                "name" => $data["name"]
            ]
        ]);
        $this->getJson('/')->assertJson([
            "data" => [
                "name" => $data["name"]
            ]
        ]);
    }
}
CODE;

    $expectedCode = <<<'CODE'
<?php
test('non phpunit assert json', function () {
    $response->assertJson([
        "data" => [
            "name" => $data["name"]
        ]
    ]);
    $this->getJson('/')->assertJson([
        "data" => [
            "name" => $data["name"]
        ]
    ]);
});
CODE;

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toBe($expectedCode);
});

it('convert assertNan to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_nan()
            {
                $this->assertNan(1);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(1)->toBeNan()');
});

it('convert assertDirectoryExists to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_directory_exists()
            {
                $this->assertDirectoryExists("/my_directory");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_directory")->toBeDirectory()');
});

it('convert assertDirectoryIsReadable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_directory_is_readable()
            {
                $this->assertDirectoryIsReadable("/my_directory");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_directory")->toBeReadableDirectory()');
});

it('convert assertDirectoryIsWritable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_directory_is_writable()
            {
                $this->assertDirectoryIsWritable("/my_directory");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_directory")->toBeWritableDirectory()');
});

it('convert assertFileExists to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_to_be_file()
            {
                $this->assertFileExists("/my_file");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_file")->toBeFile()');
});

it('convert assertFileIsReadable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_file_is_readable()
            {
                $this->assertFileIsReadable("/my_file");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_file")->toBeReadableFile()');
});

it('convert assertFileIsWritable to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_file_is_writable()
            {
                $this->assertFileIsWritable("/my_file");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("/my_file")->toBeWritableFile()');
});

it('convert assertMatchesRegularExpression to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_matches_regular_expression()
            {
                $this->assertMatchesRegularExpression("/^hello wo.*$/i", "Hello World");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("Hello World")->toMatch("/^hello wo.*$/i")');
});

it('convert assertThat to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_that()
            {
                $this->assertThat(new IsTrue(), true);
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect(true)->toMatchConstraint(new IsTrue())');
});

it('convert assertStringStartsWith to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_string_starts_with()
            {
                $this->assertStringStartsWith("Hello", "Hello World");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("Hello World")->toStartWith("Hello")');
});

it('convert assertStringEndsWith to PestExpectation', function () {
    $code = '<?php
        class MyTest {
            public function test_assert_string_ends_with()
            {
                $this->assertStringEndsWith("Hello", "Hello World");
            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->toContain('expect("Hello World")->toEndWith("Hello")');
});

it('convert @group to pest group', function () {
    $code = <<<'CODE'
<?php

class MyTest {
    /**
     * @group actions
     * @group fortify
     */
    public function test_one()
    {

    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php

test('one', function () {
})->group('actions', 'fortify');
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('convert @group to pest group from attribute', function () {
    $code = '<?php

class MyTest {
    #[Group("fortify", "actions")]
    public function test_one()
    {

    }
}
';

    $convertedCode = codeConverter()->convert($code);

    $expected = '<?php

test(\'one\', function () {
})->group(\'fortify\', \'actions\');
';

    expect($convertedCode)->toEqual($expected);
});

it('convert @depends to pest depends', function () {
    $code = '<?php
        class MyTest {
            public function test_one()
            {
                $foo = "foo";

                return $foo;
            }

            /**
             * @depends test_one
             */
            public function test_two($foo)
            {

            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('function ($foo)')
        ->not->toContain('@depends test_one')
        ->toContain("->depends('one')");
});

it('convert @depends to pest depends from attribute', function () {
    $code = '<?php
        class MyTest {
            public function test_one()
            {
                $foo = "foo";

                return $foo;
            }

            #[Depends("test_one")]
            public function test_two($foo)
            {

            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('function ($foo)')
        ->not->toContain('#[Depends("test_one")]')
        ->toContain("->depends('one')");
});

it('can convert multiple @depends to pest depends', function () {
    $code = '<?php
        class MyTest {
            public function test_one()
            {
                $foo = "foo";

                return $foo;
            }

            public function test_two()
            {
                $bar = "bar";

                return $bar;
            }

            /**
             * @depends test_one
             * @depends test_two
             */
            public function test_three($foo, $bar)
            {

            }
        }
    ';

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain('function ($foo, $bar)')
        ->not->toContain('@depends test_one')
        ->not->toContain('@depends test_two')
        ->toContain("->depends('one', 'two')");
});

it('convert custom message in expectation', function () {
    $code = '<?php
class MyTest {
    public function test_one()
    {
        $condition = false;
        $message = "My custom message";

        $this->assertTrue(
            $condition,
            $message
        );
    }
}
';

    $convertedCode = codeConverter()->convert($code);

    $expected = '<?php
test(\'one\', function () {
    $condition = false;
    $message = "My custom message";

    expect($condition)->toBeTrue($message);
});
';

    expect($convertedCode)->toEqual($expected);
});

it('convert named arguments', function () {
    $code = <<<'CODE'
<?php
class MyTest {
    public function test_named_arguments()
    {
        $this->assertEquals(
            expected: $expectedValue,
            actual: $actualValue,
        );
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    $expected = <<<'CODE'
<?php
test('named arguments', function () {
    expect(value: $actualValue)->toEqual(expected: $expectedValue);
});
CODE;

    expect($convertedCode)->toEqual($expected);
});

it('reset extends context between tests conversion', function () {
    $codeConverter = codeConverter();

    $code = <<<'CODE'
<?php

class MyTest extends MyManager
{

}
CODE;

    $codeConverter->convert($code);

    $code = <<<'CODE'
<?php

use PHPUnit\Framework\TestCase;

class OtherTest extends TestCase
{

}
CODE;

    $convertedCode = $codeConverter->convert($code);

    expect($convertedCode)->not->toContain('MyManager');
});

it('keep semicolon on group use after conversion', function () {
    $code = <<<'CODE'
<?php

namespace My\Tests;

use My\{Foo, Bar};
use My\{Hello, World};

class ResultTest extends TestCase
{

}
CODE;

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)
        ->toContain("use My\{Foo, Bar};")
        ->toContain("use My\{Hello, World};");
});

it('ignores extends from anonymous classes within tests', function () {
    $code = <<<'CODE'
<?php

namespace My\Tests;

use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    protected function setUp(): void
    {
        $anonymousClass = new class () extends \stdClass {
            public function foo(): void {}
        };
    }

    public function testSomething(): void {
        $this->assertTrue(true);
    }
}
CODE;

    $convertedCode = codeConverter()->convert($code);

    expect($convertedCode)->not->toContain('uses(');
});
