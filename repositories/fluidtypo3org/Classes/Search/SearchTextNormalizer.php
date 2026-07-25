<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search;

final class SearchTextNormalizer
{
    public function normalize(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = mb_strtolower($value, 'UTF-8');
        $value = (string)preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        return trim((string)preg_replace('/\s+/u', ' ', $value));
    }

    /**
     * @return list<string>
     */
    public function getTerms(string $normalizedQuery): array
    {
        if ($normalizedQuery === '') {
            return [];
        }

        return array_slice(
            array_values(array_unique(explode(' ', $normalizedQuery))),
            0,
            12,
        );
    }

    /**
     * @param iterable<string> $values
     */
    public function normalizeSet(iterable $values): string
    {
        $tokens = [];
        foreach ($values as $value) {
            foreach ($this->getTerms($this->normalize($value)) as $token) {
                $tokens[$token] = true;
            }
        }
        if ($tokens === []) {
            return '';
        }

        $tokens = array_keys($tokens);
        sort($tokens, SORT_STRING);
        return '|' . implode('|', $tokens) . '|';
    }
}
