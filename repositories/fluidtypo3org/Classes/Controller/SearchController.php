<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Controller;

use FluidTYPO3\FluidTYPO3Org\Search\SearchService;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[Autoconfigure(public: true)]
final class SearchController extends ActionController
{
    public function __construct(
        private readonly SearchService $searchService,
    ) {}

    public function resultsAction(): ResponseInterface
    {
        $queryValue = $this->request->getQueryParams()['q'] ?? '';
        $query = is_scalar($queryValue)
            ? trim(mb_substr((string)$queryValue, 0, 100))
            : '';
        $results = $query !== '' ? $this->searchService->search($query) : [];

        $this->view->assignMultiple([
            'query' => $query,
            'searched' => $query !== '',
            'results' => $results,
            'resultCount' => count($results),
            'indexedRecordCount' => $this->searchService->getIndexedRecordCount(),
            'documentationPageUid' => 5,
            'libraryDetailPageUid' => 9,
        ]);
        return $this->htmlResponse();
    }
}
