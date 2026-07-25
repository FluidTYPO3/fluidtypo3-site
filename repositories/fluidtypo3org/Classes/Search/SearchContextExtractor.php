<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Search;

final readonly class SearchContextExtractor
{
    private const EXTENSION_CONTEXTS = [
        'fluidcontent' => ['fluidcontent'],
        'fluidpages' => ['fluidpages'],
        'flux' => ['flux'],
        'vhs' => ['vhs'],
    ];

    private const FEATURE_CONTEXTS = [
        'composer' => ['composer'],
        'content' => ['content', 'contents', 'fce'],
        'controller' => ['controller', 'controllers'],
        'extension' => ['extension', 'extensions'],
        'fal' => ['fal'],
        'form' => ['form', 'forms', 'flexform', 'flexforms'],
        'git' => ['git', 'github'],
        'grid' => ['grid', 'grids'],
        'installation' => ['install', 'installation', 'installing'],
        'language' => ['language', 'languages', 'localization'],
        'menu' => ['menu', 'menus', 'navigation'],
        'migration' => ['migration', 'migrations'],
        'page' => ['page', 'pages'],
        'plugin' => ['plugin', 'plugins'],
        'preview' => ['preview', 'previews'],
        'provider' => ['provider', 'providers'],
        'routing' => ['route', 'routes', 'routing'],
        'tca' => ['tca'],
        'template' => ['template', 'templates', 'templating'],
        'typoscript' => ['typoscript'],
        'viewhelper' => ['viewhelper', 'viewhelpers'],
    ];

    public function __construct(
        private SearchTextNormalizer $normalizer,
    ) {}

    /**
     * @return list<string>
     */
    public function extractExtensionContexts(string $content): array
    {
        return $this->extract($content, self::EXTENSION_CONTEXTS);
    }

    /**
     * @return list<string>
     */
    public function extractFeatureContexts(string $content): array
    {
        return $this->extract($content, self::FEATURE_CONTEXTS);
    }

    /**
     * @param array<string, list<string>> $contexts
     * @return list<string>
     */
    private function extract(string $content, array $contexts): array
    {
        $normalizedContent = $this->normalizer->normalize($content);
        $terms = array_fill_keys(
            $normalizedContent === '' ? [] : explode(' ', $normalizedContent),
            true,
        );
        $matches = [];
        foreach ($contexts as $context => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($terms[$alias])) {
                    $matches[] = $context;
                    break;
                }
            }
        }
        return $matches;
    }
}
