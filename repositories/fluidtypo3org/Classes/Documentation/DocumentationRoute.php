<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation;

use FluidTYPO3\FluidTYPO3Org\Documentation\Exception\InvalidDocumentationRouteException;

final readonly class DocumentationRoute
{
    public const MAX_SEGMENTS = 3;
    public const SEGMENT_PATTERN = '[a-z0-9]+(?:-[a-z0-9]+)*';
    public const MAX_SEGMENT_LENGTH = 80;

    /** @var list<string> */
    private array $segments;

    private function __construct(string ...$segments)
    {
        if (count($segments) > self::MAX_SEGMENTS) {
            throw new InvalidDocumentationRouteException('A documentation route may contain at most three segments.');
        }

        foreach ($segments as $segment) {
            if (!self::isValidSegment($segment)) {
                throw new InvalidDocumentationRouteException('The documentation route contains an invalid segment.');
            }
        }

        $this->segments = $segments;
    }

    public static function root(): self
    {
        return new self();
    }

    public static function fromSegments(string ...$segments): self
    {
        return new self(...$segments);
    }

    public static function fromPath(string $path): self
    {
        if ($path === '') {
            return self::root();
        }

        return new self(...explode('/', $path));
    }

    public static function fromNullableSegments(
        ?string $segment1,
        ?string $segment2,
        ?string $segment3,
    ): self {
        $segments = [];
        $encounteredEmptySegment = false;

        foreach ([$segment1, $segment2, $segment3] as $segment) {
            if ($segment === null || $segment === '') {
                $encounteredEmptySegment = true;
                continue;
            }
            if ($encounteredEmptySegment) {
                throw new InvalidDocumentationRouteException('Documentation route segments must be contiguous.');
            }
            $segments[] = $segment;
        }

        return new self(...$segments);
    }

    public static function isValidSegment(string $segment): bool
    {
        return $segment !== ''
            && strlen($segment) <= self::MAX_SEGMENT_LENGTH
            && preg_match('/\A' . self::SEGMENT_PATTERN . '\z/D', $segment) === 1;
    }

    public function append(string $segment): self
    {
        return new self(...[...$this->segments, $segment]);
    }

    /**
     * @return list<string>
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * @return array{segment1?: string, segment2?: string, segment3?: string}
     */
    public function getArguments(): array
    {
        $arguments = [];
        foreach ($this->segments as $index => $segment) {
            $arguments['segment' . ($index + 1)] = $segment;
        }
        return $arguments;
    }

    public function getKey(): string
    {
        return implode('/', $this->segments);
    }

    public function getDepth(): int
    {
        return count($this->segments);
    }
}
