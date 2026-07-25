<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Controller;

use FluidTYPO3\FluidTYPO3Org\Documentation\DocumentationRoute;
use FluidTYPO3\FluidTYPO3Org\Documentation\Exception\InvalidDocumentationRouteException;
use FluidTYPO3\FluidTYPO3Org\Documentation\Rendering\GithubFlavoredMarkdownRenderer;
use FluidTYPO3\FluidTYPO3Org\Documentation\Repository\DocumentationRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[Autoconfigure(public: true)]
final class DocumentationController extends ActionController
{
    public function __construct(
        private readonly DocumentationRepositoryInterface $documentationRepository,
        private readonly GithubFlavoredMarkdownRenderer $markdownRenderer,
    ) {}

    public function navigationAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'root' => $this->documentationRepository->getRoot(),
            'detailPageUid' => (int)($this->settings['detailPageUid'] ?? 0),
        ]);
        return $this->htmlResponse();
    }

    public function detailAction(
        ?string $segment1 = null,
        ?string $segment2 = null,
        ?string $segment3 = null,
    ): ResponseInterface {
        try {
            if (
                is_string($this->settings['documentRoute'] ?? null)
                && $this->settings['documentRoute'] !== ''
            ) {
                $route = DocumentationRoute::fromPath($this->settings['documentRoute']);
            } else {
                $route = DocumentationRoute::fromNullableSegments($segment1, $segment2, $segment3);
            }
        } catch (InvalidDocumentationRouteException) {
            throw new PageNotFoundException('The requested documentation page was not found.', 1753358201);
        }

        $document = $this->documentationRepository->findDocument($route);
        if ($document !== null) {
            $viewVariables = [
                'document' => $document,
                'renderedMarkdown' => $this->markdownRenderer->render(
                    $document->getMarkdown(),
                    $this->request,
                    $route,
                ),
            ];

            $parentRouteSegments = $route->getSegments();
            array_pop($parentRouteSegments);
            $documentFolder = $this->documentationRepository->findFolder(
                DocumentationRoute::fromSegments(...$parentRouteSegments),
            );
            if (
                $documentFolder !== null
                && $documentFolder->getFolders()->isEmpty()
                && count($documentFolder->getDocuments()) > 1
            ) {
                $viewVariables['documentFolder'] = $documentFolder;
                $viewVariables['documentSiblings'] = $documentFolder->getDocuments();
            }

            $this->view->assignMultiple($viewVariables);
            return $this->htmlResponse();
        }

        $folder = $this->documentationRepository->findFolder($route);
        if ($folder === null) {
            throw new PageNotFoundException('The requested documentation page was not found.', 1753358202);
        }

        $this->view->assign('folder', $folder);
        return $this->htmlResponse();
    }
}
