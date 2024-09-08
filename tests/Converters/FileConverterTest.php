<?php

use Pest\Drift\Converters\CodeConverterFactory;
use Pest\Drift\Converters\FileConverter;
use Pest\Drift\Finder\File;
use Symfony\Component\Finder\SplFileInfo;

it('create converted file', function () {
    $splFileInfo = new SplFileInfo(
        tmpDir('sources/FooTest.php'),
        '',
        '/'
    );

    $file = new File($splFileInfo);

    $codeConverter = (new CodeConverterFactory)->codeConverter();

    (new FileConverter($codeConverter, tmpDir('results')))->convert($file);

    expect(file_exists(tmpDir('results').'/FooTest.php'))->toBeTrue();
});
