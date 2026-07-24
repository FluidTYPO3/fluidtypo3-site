<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Documentation;

use DOMDocument;
use DOMXPath;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;

final readonly class PackageDocumentationVersionProvider
{
    private const array PACKAGES = [
        [
            'key' => 'vhs',
            'name' => 'VHS',
            'handle' => 'fluidtypo3/vhs',
            'feedUrl' => 'https://github.com/FluidTYPO3/vhs/tags.atom',
            'tagsApiUrl' => 'https://api.github.com/repos/FluidTYPO3/vhs/tags?per_page=100',
            'documentationUrlPattern' => 'https://docs.typo3.org/p/fluidtypo3/vhs/%s/en-us/Index.html',
            'description' => 'Browse the ViewHelper reference and guides for each supported VHS release line.',
        ],
        [
            'key' => 'flux',
            'name' => 'Flux',
            'handle' => 'fluidtypo3/flux',
            'feedUrl' => 'https://github.com/FluidTYPO3/flux/tags.atom',
            'tagsApiUrl' => 'https://api.github.com/repos/FluidTYPO3/flux/tags?per_page=100',
            'documentationUrlPattern' => 'https://docs.typo3.org/p/fluidtypo3/flux/%s/en-us/',
            'description' => 'Find the matching Flux integration and configuration reference for your installed release.',
        ],
    ];

    public function __construct(
        private RequestFactory $requestFactory,
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
    public function getPackages(): array
    {
        $packages = [];
        foreach (self::PACKAGES as $package) {
            $versions = $this->fetchMinorVersions($package['feedUrl'], $package['tagsApiUrl']);
            $packages[] = [
                'key' => $package['key'],
                'name' => $package['name'],
                'handle' => $package['handle'],
                'description' => $package['description'],
                'versions' => $this->buildDocumentationVersions(
                    $versions,
                    $package['documentationUrlPattern'],
                ),
            ];
        }

        return $packages;
    }

    /**
     * @return list<string>
     */
    public function extractMinorVersions(string $atomFeed): array
    {
        if (trim($atomFeed) === '') {
            return [];
        }

        $document = new DOMDocument();
        if (!$document->loadXML($atomFeed, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return [];
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
        $titleNodes = $xpath->query('/atom:feed/atom:entry/atom:title');
        if ($titleNodes === false) {
            return [];
        }

        $tags = [];
        foreach ($titleNodes as $titleNode) {
            $tags[] = trim($titleNode->textContent);
        }

        return $this->extractMinorVersionsFromTags($tags);
    }

    /**
     * @param list<string> $tags
     * @return list<string>
     */
    public function extractMinorVersionsFromTags(array $tags): array
    {
        $versions = [];
        foreach ($tags as $tag) {
            if (
                preg_match(
                    '/^v?(?<major>0|[1-9]\d*)\.(?<minor>0|[1-9]\d*)(?:\.(?:0|[1-9]\d*))?$/',
                    trim($tag),
                    $matches,
                ) !== 1
            ) {
                continue;
            }

            $minorVersion = $matches['major'] . '.' . $matches['minor'];
            $versions[$minorVersion] = $minorVersion;
        }

        $versions = array_values($versions);
        usort(
            $versions,
            static fn(string $left, string $right): int => version_compare($right, $left),
        );

        return $versions;
    }

    /**
     * @return list<string>
     */
    private function fetchMinorVersions(string $feedUrl, string $tagsApiUrl): array
    {
        $versions = $this->fetchAtomMinorVersions($feedUrl);
        if (count($this->extractMajorVersions($versions)) < 3) {
            $versions = array_values(array_unique([
                ...$versions,
                ...$this->fetchApiMinorVersions($tagsApiUrl),
            ]));
            usort(
                $versions,
                static fn(string $left, string $right): int => version_compare($right, $left),
            );
        }

        $allowedMajors = array_slice($this->extractMajorVersions($versions), 0, 3);
        return array_values(array_filter(
            $versions,
            static fn(string $version): bool => in_array(strstr($version, '.', true), $allowedMajors, true),
        ));
    }

    /**
     * @return list<string>
     */
    private function fetchAtomMinorVersions(string $feedUrl): array
    {
        try {
            $response = $this->requestFactory->request(
                $feedUrl,
                'GET',
                [
                    'connect_timeout' => 2.5,
                    'timeout' => 5.0,
                    'headers' => [
                        'Accept' => 'application/atom+xml',
                    ],
                ],
            );

            return $this->extractMinorVersions((string)$response->getBody());
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<string>
     */
    private function fetchApiMinorVersions(string $tagsApiUrl): array
    {
        try {
            $response = $this->requestFactory->request(
                $tagsApiUrl,
                'GET',
                [
                    'connect_timeout' => 2.5,
                    'timeout' => 5.0,
                    'headers' => [
                        'Accept' => 'application/vnd.github+json',
                        'User-Agent' => 'fluidtypo3.org-documentation-versions',
                        'X-GitHub-Api-Version' => '2022-11-28',
                    ],
                ],
            );
            $tags = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($tags)) {
                return [];
            }

            $tagNames = [];
            foreach ($tags as $tag) {
                if (is_array($tag) && is_string($tag['name'] ?? null)) {
                    $tagNames[] = $tag['name'];
                }
            }

            return $this->extractMinorVersionsFromTags($tagNames);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param list<string> $versions
     * @return list<string>
     */
    private function extractMajorVersions(array $versions): array
    {
        $majorVersions = [];
        foreach ($versions as $version) {
            $majorVersion = strstr($version, '.', true);
            if ($majorVersion !== false) {
                $majorVersions[$majorVersion] = $majorVersion;
            }
        }

        return array_values($majorVersions);
    }

    /**
     * @param list<string> $versions
     * @return list<array{version: string, url: string, isMajorRelease: bool}>
     */
    private function buildDocumentationVersions(array $versions, string $documentationUrlPattern): array
    {
        $documentationVersions = [];
        foreach ($versions as $index => $version) {
            $nextVersion = $versions[$index + 1] ?? null;
            $currentMajor = strstr($version, '.', true);
            $nextMajor = $nextVersion !== null ? strstr($nextVersion, '.', true) : null;
            $documentationVersions[] = [
                'version' => $version,
                'url' => sprintf($documentationUrlPattern, $version),
                'isMajorRelease' => $nextVersion === null || $currentMajor !== $nextMajor,
            ];
        }

        return $documentationVersions;
    }
}
