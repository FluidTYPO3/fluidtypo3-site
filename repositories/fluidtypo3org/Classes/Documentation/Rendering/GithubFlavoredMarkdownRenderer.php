<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Rendering;

use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GithubFlavoredMarkdownRenderer
{
    public function __construct(
        private DocumentationImageProcessor $imageProcessor,
        private DocumentationHeadingProcessor $headingProcessor,
        private DocumentationLinkProcessor $linkProcessor,
    ) {}

    public function render(
        string $markdown,
        ?ServerRequestInterface $request,
        DocumentationRoute $currentRoute,
    ): string {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $converter->getEnvironment()->addEventListener(
            DocumentParsedEvent::class,
            fn(DocumentParsedEvent $event) => $this->imageProcessor->process($event, $request),
        );
        $converter->getEnvironment()->addEventListener(
            DocumentParsedEvent::class,
            $this->headingProcessor->process(...),
        );
        $converter->getEnvironment()->addEventListener(
            DocumentParsedEvent::class,
            fn(DocumentParsedEvent $event) => $this->linkProcessor->process(
                $event,
                $request,
                $currentRoute,
            ),
        );
        return (string)$converter->convert($markdown);
    }
}
