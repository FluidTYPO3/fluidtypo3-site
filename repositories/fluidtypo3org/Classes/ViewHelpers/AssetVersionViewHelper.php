<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\ViewHelpers;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class AssetVersionViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('path', 'string', 'Path relative to the public directory.', true);
    }

    public function render(): string
    {
        $path = Environment::getPublicPath() . '/' . ltrim((string)$this->arguments['path'], '/');
        $modifiedAt = is_file($path) ? filemtime($path) : false;

        return $modifiedAt === false ? '0' : (string)$modifiedAt;
    }
}
