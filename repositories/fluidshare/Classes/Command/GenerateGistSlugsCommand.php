<?php

declare(strict_types=1);

namespace FluidTYPO3\Fluidshare\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Attribute\AsNonSchedulableCommand;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

#[AsCommand(
    'fluidshare:generate-slugs',
    'Generate unique slugs for Fluidshare library entries.',
)]
#[AsNonSchedulableCommand]
final class GenerateGistSlugsCommand extends Command
{
    private const TABLE_NAME = 'tx_fluidshare_domain_model_gist';
    private const FIELD_NAME = 'slug';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly CacheManager $cacheManager,
        private readonly TcaSchemaFactory $tcaSchemaFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Regenerate slugs that already contain a value.',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show generated values without updating records.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (
            !$this->tcaSchemaFactory->has(self::TABLE_NAME)
            || !$this->tcaSchemaFactory->get(self::TABLE_NAME)->hasField(self::FIELD_NAME)
        ) {
            $io->error(sprintf(
                'No TCA slug configuration exists for %s.%s.',
                self::TABLE_NAME,
                self::FIELD_NAME,
            ));
            return Command::FAILURE;
        }
        $fieldConfiguration = $this->tcaSchemaFactory
            ->get(self::TABLE_NAME)
            ->getField(self::FIELD_NAME)
            ->getConfiguration();
        if (($fieldConfiguration['type'] ?? null) !== 'slug') {
            $io->error(sprintf(
                'The TCA field %s.%s is not configured as a slug.',
                self::TABLE_NAME,
                self::FIELD_NAME,
            ));
            return Command::FAILURE;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction());
        $records = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $overwrite = (bool)$input->getOption('overwrite');
        $dryRun = (bool)$input->getOption('dry-run');
        $slugHelper = new SlugHelper(
            self::TABLE_NAME,
            self::FIELD_NAME,
            $fieldConfiguration,
        );
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $generated = 0;
        $skipped = 0;
        $unchanged = 0;

        foreach ($records as $record) {
            $currentSlug = trim((string)($record[self::FIELD_NAME] ?? ''));
            if (!$overwrite && $currentSlug !== '') {
                $skipped++;
                continue;
            }

            $slug = $slugHelper->generate($record, (int)$record['pid']);
            $recordState = RecordStateFactory::forName(self::TABLE_NAME)->fromArray($record);
            if (!$slugHelper->isUniqueInSite($slug, $recordState)) {
                $slug = $slugHelper->buildSlugForUniqueInSite($slug, $recordState);
            }

            if ($currentSlug === $slug) {
                $unchanged++;
                continue;
            }

            if ($dryRun) {
                $io->writeln(sprintf(
                    '<comment>%d</comment> %s',
                    (int)$record['uid'],
                    $slug,
                ));
            } else {
                $connection->update(
                    self::TABLE_NAME,
                    [
                        self::FIELD_NAME => $slug,
                        'tstamp' => time(),
                    ],
                    ['uid' => (int)$record['uid']],
                );
            }
            $generated++;
        }

        if (!$dryRun && $generated > 0) {
            $this->cacheManager->flushCachesInGroup('pages');
        }

        $verb = $dryRun ? 'Would generate' : 'Generated';
        $io->success(sprintf(
            '%s %d slug(s); skipped %d existing and found %d unchanged.',
            $verb,
            $generated,
            $skipped,
            $unchanged,
        ));
        return Command::SUCCESS;
    }
}
