<?php

declare(strict_types=1);

namespace FluidTYPO3\Fluidshare\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class GistUrlValidator extends AbstractValidator
{
    public const ERROR_CODE = 1221565130;

    protected function isValid(mixed $value): void
    {
        if (!is_string($value) || !self::isGistUrl($value)) {
            $this->addError(
                'The URL must identify a public GitHub Gist.',
                self::ERROR_CODE,
            );
        }
    }

    public static function isGistUrl(string $url): bool
    {
        if ($url === '' || trim($url) !== $url) {
            return false;
        }

        $parts = parse_url($url);
        if (
            $parts === false
            || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
            || strtolower((string)($parts['host'] ?? '')) !== 'gist.github.com'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            return false;
        }

        return preg_match(
            '#^/[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?/[0-9a-f]{1,64}/?$#D',
            (string)($parts['path'] ?? ''),
        ) === 1;
    }
}
