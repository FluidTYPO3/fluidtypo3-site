<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Updates;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Upgrades\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

#[UpgradeWizard('fluidtypo3orgMigrateSolrSearchPlugin')]
final readonly class MigrateSolrSearchPlugin implements UpgradeWizardInterface
{
    private const TABLE_NAME = 'tt_content';
    private const OLD_CONTENT_TYPE = 'solr_pi_results';
    private const NEW_CONTENT_TYPE = 'fluidtypo3org_search';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function getTitle(): string
    {
        return 'Migrate the FluidTYPO3.org search plugin';
    }

    public function getDescription(): string
    {
        return 'Replace Solr result content elements with the local FluidTYPO3.org search plugin.';
    }

    public function updateNecessary(): bool
    {
        return $this->countRecordsToMigrate() > 0;
    }

    /**
     * @return list<class-string>
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function executeUpdate(): bool
    {
        $this->connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->update(
                self::TABLE_NAME,
                ['CType' => self::NEW_CONTENT_TYPE],
                ['CType' => self::OLD_CONTENT_TYPE],
            );
        return true;
    }

    private function countRecordsToMigrate(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        return (int)$queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter(self::OLD_CONTENT_TYPE),
                ),
            )
            ->executeQuery()
            ->fetchOne();
    }
}
