<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Rendering;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GithubFlavoredMarkdownRenderer
{
    public function __construct(
        private DocumentationImageProcessor $imageProcessor,
    ) {}

    public function render(string $markdown, ?ServerRequestInterface $request = null): string
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $converter->getEnvironment()->addEventListener(
            DocumentParsedEvent::class,
            fn(DocumentParsedEvent $event) => $this->imageProcessor->process($event, $request),
        );
        return (string)$converter->convert($markdown);
    }
}
