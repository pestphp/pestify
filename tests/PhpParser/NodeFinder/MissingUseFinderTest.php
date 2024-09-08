<?php

use Pest\Drift\Parser\NodeFinder\MissingUseFinder;
use Pest\Drift\Parser\NodeFinder\NameFinder;
use Pest\Drift\Parser\NodeFinder\UseFinder;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeFinder;

function missingUseFinder()
{
    return new MissingUseFinder(
        new UseFinder(new NodeFinder),
        new NameFinder(new NodeFinder),
    );
}

it('does not return duplicates', function () {
    $nodes = [
        new UseUse(new Name('Hello\World')),
        new Name('Bar', ['resolvedName' => new FullyQualified('Foo\Bar'), 'startLine' => 1]),
        new Name('Bar', ['resolvedName' => new FullyQualified('Foo\Bar'), 'startLine' => 3]),
        new Name('World', ['resolvedName' => new FullyQualified('Hello\World')]),
    ];

    $missingUses = missingUseFinder()->find($nodes);

    expect($missingUses)->toHaveCount(1);
});

it('does not flag use in group as missing', function () {
    $groupUse = new GroupUse(new Name('Foo'), []);
    $groupUse->uses[] = new UseUse(new Name('Bar'), attributes: [
        'parent' => $groupUse,
    ]);

    $nodes = [
        $groupUse,
        new Name('Bar', ['resolvedName' => new FullyQualified('Foo\Bar'), 'startLine' => 1]),
    ];

    $missingUses = missingUseFinder()->find($nodes);

    expect($missingUses)->toHaveCount(0);
});
