<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Command;

use FluidTYPO3\FluidTYPO3Org\Search\Index\SearchIndexBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    'fluidtypo3org:search:rebuild',
    'Rebuild the complete documentation and Library search index.',
)]
final class RebuildSearchIndexCommand extends Command
{
    public function __construct(
        private readonly SearchIndexBuilder $searchIndexBuilder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->searchIndexBuilder->rebuild();

        $rows = [];
        foreach ($result->getCountsByType() as $type => $count) {
            $rows[] = [$type, $count];
        }
        $io->table(['Type', 'Indexed records'], $rows);
        $io->success(sprintf(
            'Rebuilt the search index with %d record(s).',
            $result->getTotal(),
        ));
        return Command::SUCCESS;
    }
}
