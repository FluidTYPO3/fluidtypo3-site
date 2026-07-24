<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Rendering;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\SystemResource\Exception\CanNotGenerateUriException;
use TYPO3\CMS\Core\SystemResource\Exception\CanNotResolvePublicResourceException;
use TYPO3\CMS\Core\SystemResource\Exception\CanNotResolveSystemResourceException;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;

final readonly class DocumentationImageProcessor
{
    private const PUBLIC_IMAGE_PATH = 'EXT:fluidtypo3org/Resources/Public/Images/';

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'svgz', 'webp'];

    public function __construct(
        private SystemResourceFactory $systemResourceFactory,
        private SystemResourcePublisherInterface $resourcePublisher,
    ) {}

    public function process(DocumentParsedEvent $event, ?ServerRequestInterface $request): void
    {
        $walker = $event->getDocument()->walker();
        while (($walkerEvent = $walker->next()) !== null) {
            $node = $walkerEvent->getNode();
            if (!$walkerEvent->isEntering() || !$node instanceof Image) {
                continue;
            }

            $relativeImagePath = $this->extractRelativeImagePath($node->getUrl());
            if ($relativeImagePath === null) {
                continue;
            }

            try {
                $resource = $this->systemResourceFactory->createPublicResource(
                    self::PUBLIC_IMAGE_PATH . $relativeImagePath
                );
                $uri = $this->resourcePublisher->generateUri(
                    $resource,
                    $request,
                    new UriGenerationOptions(cacheBusting: true),
                );
            } catch (
                CanNotGenerateUriException
                | CanNotResolvePublicResourceException
                | CanNotResolveSystemResourceException
            ) {
                continue;
            }

            $node->setUrl((string)$uri);
        }
    }

    private function extractRelativeImagePath(string $url): ?string
    {
        if (preg_match(
            '#\A(?:\.\./)*Images/(?<path>[A-Za-z0-9][A-Za-z0-9._/-]*)\z#D',
            $url,
            $matches,
        ) !== 1) {
            return null;
        }

        $path = $matches['path'];
        $segments = explode('/', $path);
        if (in_array('', $segments, true) || in_array('.', $segments, true) || in_array('..', $segments, true)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return null;
        }

        return $path;
    }
}
