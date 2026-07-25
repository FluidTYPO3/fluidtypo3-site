<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Donation;

use DateTimeImmutable;
use TYPO3\CMS\Core\Database\ConnectionPool;

final readonly class DonationHistoryProvider
{
    private const TABLE = 'tx_fluidtypo3org_donation';
    private const MONTH_COUNT = 6;

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @return list<array{
     *     key: string,
     *     date: string,
     *     label: string,
     *     received: string,
     *     withdrawn: string,
     *     remaining: string,
     *     hasActivity: bool
     * }>
     */
    public function getHistory(?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable();
        $currentMonthStart = $now
            ->setTime(0, 0)
            ->modify('first day of this month');
        $firstMonthStart = $currentMonthStart->modify('-' . self::MONTH_COUNT . ' months');

        $months = [];
        for ($offset = 0; $offset < self::MONTH_COUNT; ++$offset) {
            $month = $firstMonthStart->modify('+' . $offset . ' months');
            $key = $month->format('Y-m');
            $months[$key] = [
                'key' => $key,
                'date' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'receivedCents' => 0,
                'withdrawnCents' => 0,
                'remainingCents' => 0,
            ];
        }

        $balanceCents = 0;
        foreach ($this->findBefore($currentMonthStart) as $donation) {
            $amountCents = $this->decimalToCents((string)$donation['amount']);
            $date = (new DateTimeImmutable())->setTimestamp((int)$donation['donation_date']);

            if ($date < $firstMonthStart) {
                $balanceCents += $amountCents;
                continue;
            }

            $key = $date->format('Y-m');
            if (!isset($months[$key])) {
                continue;
            }

            if ($amountCents >= 0) {
                $months[$key]['receivedCents'] += $amountCents;
            } else {
                $months[$key]['withdrawnCents'] += abs($amountCents);
            }
        }

        foreach ($months as &$month) {
            $balanceCents += $month['receivedCents'] - $month['withdrawnCents'];
            $month['remainingCents'] = $balanceCents;
        }
        unset($month);

        return array_values(array_reverse(array_map(
            fn(array $month): array => [
                'key' => $month['key'],
                'date' => $month['date'],
                'label' => $month['label'],
                'received' => $this->formatCents($month['receivedCents']),
                'withdrawn' => $this->formatCents($month['withdrawnCents']),
                'remaining' => $this->formatCents($month['remainingCents']),
                'hasActivity' => $month['receivedCents'] !== 0 || $month['withdrawnCents'] !== 0,
            ],
            $months,
        )));
    }

    /**
     * @return list<array{donation_date: int|string, amount: int|float|string}>
     */
    private function findBefore(DateTimeImmutable $exclusiveEnd): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('donation_date', 'amount')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->lt(
                    'donation_date',
                    $queryBuilder->createNamedParameter($exclusiveEnd->getTimestamp()),
                ),
            )
            ->orderBy('donation_date')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function decimalToCents(string $amount): int
    {
        if (preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches) !== 1) {
            throw new \UnexpectedValueException('Invalid donation amount "' . $amount . '".', 1784994025);
        }

        $cents = ((int)$matches[2] * 100) + (int)str_pad($matches[3] ?? '', 2, '0');
        return $matches[1] === '-' ? -$cents : $cents;
    }

    private function formatCents(int $cents): string
    {
        $decimals = $cents % 100 === 0 ? 0 : 2;
        return '€' . number_format($cents / 100, $decimals, '.', ',');
    }
}
