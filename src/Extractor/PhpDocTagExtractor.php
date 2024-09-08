<?php

declare(strict_types=1);

namespace Pest\Drift\Extractor;

use PhpParser\Comment;

final class PhpDocTagExtractor
{
    /**
     * @param  array<int, Comment>  $comments
     * @return array<string, array<int, string>>
     */
    public function fromComments(array $comments): array
    {
        $tags = [];

        foreach ($comments as $comment) {
            preg_match_all('/(@[^\s\n]*) ?([^\s\n]*) *$/m', $comment->getText(), $matches);
            $itemsCount = is_countable($matches[1]) ? count($matches[1]) : 0; // @phpstan-ignore-line

            for ($i = 0; $i < $itemsCount; $i++) {
                $key = $matches[1][$i];
                $value = $matches[2][$i];

                if (! array_key_exists($key, $tags)) {
                    $tags[$key] = [];
                }

                $tags[$key][] = $value;
            }
        }

        return $tags;
    }
}
