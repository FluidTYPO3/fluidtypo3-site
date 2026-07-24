<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Integration\Routing;

use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

final class SafeDocumentationSegmentMapper implements StaticMappableAspectInterface
{
    public function generate(string $value): ?string
    {
        return DocumentationRoute::isValidSegment($value) ? $value : null;
    }

    public function resolve(string $value): ?string
    {
        return DocumentationRoute::isValidSegment($value) ? $value : null;
    }
}
