<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation;

final readonly class Document
{
    public function __construct(
        private string $title,
        private string $number,
        private string $slug,
        private DocumentationRoute $route,
        private string $markdown,
        private string $excerpt,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getRoute(): DocumentationRoute
    {
        return $this->route;
    }

    public function getMarkdown(): string
    {
        return $this->markdown;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }
}
