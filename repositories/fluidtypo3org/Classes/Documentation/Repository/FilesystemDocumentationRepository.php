<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation\Repository;

use FluidTYPO3\FluidTYPO3Org\Documentation\Collection\DocumentCollection;
use FluidTYPO3\FluidTYPO3Org\Documentation\Collection\FolderCollection;
use FluidTYPO3\FluidTYPO3Org\Documentation\Document;
use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use FluidTYPO3\FluidTYPO3Org\Documentation\Folder;

final class FilesystemDocumentationRepository implements DocumentationRepositoryInterface
{
    private const MAX_FOLDER_DEPTH = 2;
    private const MAX_DOCUMENT_BYTES = 2_000_000;

    private ?Folder $root = null;

    /** @var array<string, Folder> */
    private array $foldersByRoute = [];

    /** @var array<string, Document> */
    private array $documentsByRoute = [];

    public function getRoot(): Folder
    {
        $this->initialize();
        return $this->root;
    }

    public function findFolder(DocumentationRoute $route): ?Folder
    {
        $this->initialize();
        return $this->foldersByRoute[$route->getKey()] ?? null;
    }

    public function findDocument(DocumentationRoute $route): ?Document
    {
        $this->initialize();
        return $this->documentsByRoute[$route->getKey()] ?? null;
    }

    private function initialize(): void
    {
        if ($this->root instanceof Folder) {
            return;
        }

        $basePath = realpath(dirname(__DIR__, 3) . '/Resources/Private/Documentation');
        if ($basePath === false || !is_dir($basePath)) {
            throw new \RuntimeException('The documentation collection is unavailable.', 1753358101);
        }

        $this->root = $this->buildFolder(
            $basePath,
            $basePath,
            'Documentation',
            '',
            '',
            DocumentationRoute::root(),
            0,
        );
    }

    private function buildFolder(
        string $basePath,
        string $directory,
        string $title,
        string $number,
        string $slug,
        DocumentationRoute $route,
        int $depth,
    ): Folder {
        $folderItems = [];
        $documentItems = [];
        $entries = scandir($directory);
        if ($entries === false) {
            throw new \RuntimeException('A documentation folder could not be read.', 1753358102);
        }
        natcasesort($entries);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;
            if (is_link($path)) {
                continue;
            }

            if (is_dir($path)) {
                $nameParts = $this->extractNameParts($entry);
                if ($nameParts === null) {
                    continue;
                }
                if ($depth >= self::MAX_FOLDER_DEPTH) {
                    throw new \RuntimeException('The documentation collection exceeds the supported folder depth.', 1753358103);
                }
                $textualName = $nameParts['textualName'];
                $childSlug = $this->slugify($textualName);
                $childRoute = $route->append($childSlug);
                $folderItems[] = $this->buildFolder(
                    $basePath,
                    $this->resolveContainedPath($basePath, $path),
                    $this->humanize($textualName),
                    $nameParts['number'],
                    $childSlug,
                    $childRoute,
                    $depth + 1,
                );
                continue;
            }

            if (!is_file($path) || strtolower(pathinfo($entry, PATHINFO_EXTENSION)) !== 'md') {
                continue;
            }

            $nameParts = $this->extractNameParts(pathinfo($entry, PATHINFO_FILENAME));
            if ($nameParts === null) {
                continue;
            }
            $textualName = $nameParts['textualName'];
            $documentPath = $this->resolveContainedPath($basePath, $path);
            $size = filesize($documentPath);
            if ($size === false || $size > self::MAX_DOCUMENT_BYTES) {
                throw new \RuntimeException('A documentation file exceeds the supported size.', 1753358104);
            }
            $markdown = file_get_contents($documentPath);
            if ($markdown === false) {
                throw new \RuntimeException('A documentation file could not be read.', 1753358105);
            }

            $documentSlug = $this->slugify($textualName);
            $documentRoute = $route->append($documentSlug);
            $heading = $this->extractDocumentHeading(
                $markdown,
                $nameParts['number'],
                $this->humanize($textualName),
            );
            $detailMarkdown = $this->stripTitleNumber($markdown);
            $document = new Document(
                $heading['title'],
                $heading['number'],
                $documentSlug,
                $documentRoute,
                $detailMarkdown,
                $this->createExcerpt($detailMarkdown),
            );
            if (isset($this->documentsByRoute[$documentRoute->getKey()])) {
                throw new \RuntimeException('The documentation collection contains duplicate routes.', 1753358106);
            }
            if (isset($this->foldersByRoute[$documentRoute->getKey()])) {
                throw new \RuntimeException('A documentation route is used by both a folder and a document.', 1753358110);
            }
            $this->documentsByRoute[$documentRoute->getKey()] = $document;
            $documentItems[] = $document;
        }

        $folder = new Folder(
            $title,
            $number,
            $slug,
            $route,
            new FolderCollection(...$folderItems),
            new DocumentCollection(...$documentItems),
        );
        if (isset($this->foldersByRoute[$route->getKey()])) {
            throw new \RuntimeException('The documentation collection contains duplicate routes.', 1753358107);
        }
        if (isset($this->documentsByRoute[$route->getKey()])) {
            throw new \RuntimeException('A documentation route is used by both a folder and a document.', 1753358111);
        }
        $this->foldersByRoute[$route->getKey()] = $folder;
        return $folder;
    }

    private function resolveContainedPath(string $basePath, string $path): string
    {
        $realPath = realpath($path);
        if ($realPath === false || !str_starts_with($realPath, rtrim($basePath, '/') . '/')) {
            throw new \RuntimeException('A documentation entry resolves outside the collection.', 1753358108);
        }
        return $realPath;
    }

    /**
     * @return array{number: string, textualName: string}|null
     */
    private function extractNameParts(string $name): ?array
    {
        if (
            preg_match(
                '/\A(?<number>\d+(?:\.\d+)*)\.(?<name>[A-Za-z][A-Za-z0-9_-]*)\z/D',
                $name,
                $matches,
            ) !== 1
        ) {
            return null;
        }
        return [
            'number' => $matches['number'],
            'textualName' => $matches['name'],
        ];
    }

    /**
     * @return array{number: string, title: string}
     */
    private function extractDocumentHeading(
        string $markdown,
        string $fallbackNumber,
        string $fallbackTitle,
    ): array {
        if (preg_match('/\A(?:\xEF\xBB\xBF)?(?<line>[^\r\n]*)/u', $markdown, $matches) !== 1) {
            return ['number' => $fallbackNumber, 'title' => $fallbackTitle];
        }

        $heading = trim($matches['line']);
        $heading = preg_replace('/\A#{1,6}[ \t]+/u', '', $heading);
        $heading = preg_replace('/[ \t]+#+\z/u', '', (string)$heading);
        $heading = trim((string)$heading);
        if ($heading === '') {
            return ['number' => $fallbackNumber, 'title' => $fallbackTitle];
        }

        if (
            preg_match(
                '/\A(?<number>\d+(?:\.\d+)*)(?:\.)?[ \t]+(?<title>.+)\z/u',
                $heading,
                $headingParts,
            ) === 1
        ) {
            return [
                'number' => $fallbackNumber,
                'title' => trim($headingParts['title']),
            ];
        }

        return ['number' => $fallbackNumber, 'title' => $heading];
    }

    private function stripTitleNumber(string $markdown): string
    {
        return (string)preg_replace_callback(
            '/\A(?<prefix>(?:\xEF\xBB\xBF)?[ \t]*(?:#{1,6}[ \t]+)?)'
                . '\d+(?:\.\d+)*(?:\.)?[ \t]+/u',
            static fn(array $matches): string => $matches['prefix'],
            $markdown,
            1,
        );
    }

    private function humanize(string $name): string
    {
        $words = preg_replace('/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $name);
        $words = preg_replace('/[_-]+/', ' ', (string)$words);
        return trim((string)$words);
    }

    private function slugify(string $name): string
    {
        $slug = strtolower($this->humanize($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string)$slug, '-');
        if (!DocumentationRoute::isValidSegment($slug)) {
            throw new \RuntimeException('A documentation name cannot be converted to a safe route segment.', 1753358109);
        }
        return $slug;
    }

    private function createExcerpt(string $markdown): string
    {
        $markdown = preg_replace('/\A\s*(?:#{1,6}\s+[^\r\n]+|[^\r\n]+\R[=-]{2,})\s*/u', '', $markdown, 1);
        $markdown = preg_replace('/```.*?```|~~~.*?~~~/su', ' ', (string)$markdown);
        $markdown = preg_replace('/!\[[^\]]*]\([^)]*\)/u', ' ', (string)$markdown);
        $markdown = preg_replace('/\[([^\]]+)]\([^)]*\)/u', '$1', (string)$markdown);
        $markdown = preg_replace('/[`*_>#|~-]+/u', ' ', (string)$markdown);
        $markdown = preg_replace('/\s+/u', ' ', (string)$markdown);
        $excerpt = trim(strip_tags((string)$markdown));

        if (mb_strlen($excerpt) <= 240) {
            return $excerpt;
        }

        $excerpt = mb_substr($excerpt, 0, 241);
        $lastSpace = mb_strrpos($excerpt, ' ');
        if ($lastSpace !== false) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace);
        }
        return rtrim($excerpt, " \t\n\r\0\x0B.,;:") . '…';
    }
}
