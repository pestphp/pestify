<?php

declare(strict_types=1);

namespace Pest\Drift\Rules\Assertions;

use Pest\Drift\Rules\AbstractConvertMethodCall;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\VariadicPlaceholder;

/**
 * @internal
 */
abstract class AbstractAssertionToExpectation extends AbstractConvertMethodCall
{
    public function __construct(
        protected string $oldName,
        protected string $newName,
        protected int $argumentCount,
    ) {}

    protected function apply(MethodCall $methodCall): int|Node|array|null
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $assertionName = $methodCall->name->name;

        if ($assertionName !== $this->oldName) {
            return null;
        }

        if ($methodCall->var instanceof Expr\Variable && $methodCall->var->name != 'this') {
            return null;
        }

        if ($methodCall->var instanceof Expr\MethodCall) {
            return null;
        }

        return new MethodCall(
            $this->buildExpect($methodCall),
            new Identifier($this->newName),
            $this->expected($methodCall->args)
        );
    }

    protected function buildExpect(MethodCall $methodCall): Expr
    {
        return new FuncCall(
            new Name('expect'),
            [
                $this->actual($methodCall->args),
            ]
        );
    }

    /**
     * Extract the expected arguments.
     *
     * @param  array<Arg|VariadicPlaceholder>  $args
     * @return array<Arg|VariadicPlaceholder>
     */
    private function expected(array $args): array
    {
        $actualPosition = $this->argumentCount >= 3 ? 1 : 0;

        unset($args[$actualPosition]);

        return $args;
    }

    /**
     * Extract the actual argument to test.
     *
     * @param  array<Arg|VariadicPlaceholder>  $args
     */
    private function actual(array $args): Arg|VariadicPlaceholder
    {
        $actualPosition = $this->argumentCount >= 3 ? 1 : 0;

        $actualArgument = $args[$actualPosition];

        if ($actualArgument instanceof Arg && $actualArgument->name instanceof Identifier) {
            $actualArgument->name->name = 'value';
        }

        return $actualArgument;
    }
}
