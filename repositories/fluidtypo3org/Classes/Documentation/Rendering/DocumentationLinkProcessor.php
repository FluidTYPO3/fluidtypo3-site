<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Rendering;

use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DocumentationLinkProcessor
{
    public function process(
        DocumentParsedEvent $event,
        ?ServerRequestInterface $request,
        DocumentationRoute $currentRoute,
    ): void {
        if ($request === null) {
            return;
        }

        $walker = $event->getDocument()->walker();
        while (($walkerEvent = $walker->next()) !== null) {
            $node = $walkerEvent->getNode();
            if (!$walkerEvent->isEntering() || !$node instanceof Link) {
                continue;
            }

            $url = $this->resolveUrl($node->getUrl(), $request, $currentRoute);
            if ($url !== null) {
                $node->setUrl($url);
            }
        }
    }

    private function resolveUrl(
        string $url,
        ServerRequestInterface $request,
        DocumentationRoute $currentRoute,
    ): ?string {
        if (
            $url === ''
            || str_starts_with($url, '/')
            || str_starts_with($url, '#')
            || str_contains($url, '://')
        ) {
            return null;
        }

        $fragment = '';
        if (str_contains($url, '#')) {
            [$url, $fragment] = explode('#', $url, 2);
            $fragment = '#' . $fragment;
        }
        if (!str_ends_with(strtolower($url), '.md')) {
            return null;
        }

        $targetSegments = $currentRoute->getSegments();
        array_pop($targetSegments);

        foreach (explode('/', $url) as $pathSegment) {
            if ($pathSegment === '' || $pathSegment === '.') {
                continue;
            }
            if ($pathSegment === '..') {
                if ($targetSegments === []) {
                    return null;
                }
                array_pop($targetSegments);
                continue;
            }

            $name = str_ends_with(strtolower($pathSegment), '.md')
                ? pathinfo($pathSegment, PATHINFO_FILENAME)
                : $pathSegment;
            $slug = $this->slugifyNumberedName(rawurldecode($name));
            if ($slug === null) {
                return null;
            }
            $targetSegments[] = $slug;
        }

        if ($targetSegments === [] || count($targetSegments) > DocumentationRoute::MAX_SEGMENTS) {
            return null;
        }

        $currentSegments = $currentRoute->getSegments();
        $currentSuffix = '/' . implode('/', $currentSegments);
        $requestPath = $request->getUri()->getPath();
        if (!str_ends_with($requestPath, $currentSuffix)) {
            return null;
        }

        $detailPagePath = substr($requestPath, 0, -strlen($currentSuffix));
        return rtrim($detailPagePath, '/')
            . '/'
            . implode('/', $targetSegments)
            . $fragment;
    }

    private function slugifyNumberedName(string $name): ?string
    {
        if (
            preg_match(
                '/\A\d+(?:\.\d+)*\.(?<name>[A-Za-z][A-Za-z0-9_-]*)\z/D',
                $name,
                $matches,
            ) !== 1
        ) {
            return null;
        }

        $words = preg_replace(
            '/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/',
            ' ',
            $matches['name'],
        );
        $slug = strtolower((string)preg_replace('/[_-]+/', ' ', (string)$words));
        $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', $slug), '-');

        return DocumentationRoute::isValidSegment($slug) ? $slug : null;
    }
}
