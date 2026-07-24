<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Backend\Form;

use FluidTYPO3\FluidTYPO3Org\Documentation\Folder;
use FluidTYPO3\FluidTYPO3Org\Documentation\Repository\DocumentationRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
final readonly class DocumentationFileItemsProvider
{
    public function __construct(
        private DocumentationRepositoryInterface $documentationRepository,
    ) {}

    /**
     * @param array{items: list<array<string, string>>} $parameters
     */
    public function addItems(array &$parameters): void
    {
        $parameters['items'][] = [
            'label' => 'Use the documentation route from the URL',
            'value' => '',
        ];

        $this->addFolderItems(
            $parameters['items'],
            $this->documentationRepository->getRoot(),
            [],
        );
    }

    /**
     * @param list<array<string, string>> $items
     * @param list<string> $parentLabels
     */
    private function addFolderItems(array &$items, Folder $folder, array $parentLabels): void
    {
        foreach ($folder->getDocuments() as $document) {
            $documentLabel = trim($document->getNumber() . ' ' . $document->getTitle());
            $items[] = [
                'label' => implode(' / ', [...$parentLabels, $documentLabel]),
                'value' => $document->getRoute()->getKey(),
            ];
        }

        foreach ($folder->getFolders() as $childFolder) {
            $folderLabel = trim($childFolder->getNumber() . ' ' . $childFolder->getTitle());
            $this->addFolderItems(
                $items,
                $childFolder,
                [...$parentLabels, $folderLabel],
            );
        }
    }
}
