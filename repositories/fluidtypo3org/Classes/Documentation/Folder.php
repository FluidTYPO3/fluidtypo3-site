<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation;

use FluidTYPO3\FluidTYPO3Org\Documentation\Collection\DocumentCollection;
use FluidTYPO3\FluidTYPO3Org\Documentation\Collection\FolderCollection;

final readonly class Folder
{
    public function __construct(
        private string $title,
        private string $number,
        private string $slug,
        private DocumentationRoute $route,
        private FolderCollection $folders,
        private DocumentCollection $documents,
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

    public function getFolders(): FolderCollection
    {
        return $this->folders;
    }

    public function getDocuments(): DocumentCollection
    {
        return $this->documents;
    }
}
