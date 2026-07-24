<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\ViewHelpers;

use FluidTYPO3\FluidTYPO3Org\Documentation\PackageDocumentationVersionProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class DocumentationVersionsViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        private readonly PackageDocumentationVersionProvider $versionProvider,
    ) {}

    /**
     * @return list<array{
     *     key: string,
     *     name: string,
     *     handle: string,
     *     description: string,
     *     versions: list<array{version: string, url: string, isMajorRelease: bool}>
     * }>
     */
    public function render(): array
    {
        return $this->versionProvider->getPackages();
    }
}
