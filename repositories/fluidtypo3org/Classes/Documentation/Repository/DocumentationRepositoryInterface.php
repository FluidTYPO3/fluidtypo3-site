<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Repository;

use FluidTYPO3\FluidTYPO3Org\Documentation\Document;
use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use FluidTYPO3\FluidTYPO3Org\Documentation\Folder;

interface DocumentationRepositoryInterface
{
    public function getRoot(): Folder;

    public function findFolder(DocumentationRoute $route): ?Folder;

    public function findDocument(DocumentationRoute $route): ?Document;
}
