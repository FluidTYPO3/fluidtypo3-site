<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\ViewHelpers;

use FluidTYPO3\FluidTYPO3Org\Donation\DonationHistoryProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class DonationHistoryViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        private readonly DonationHistoryProvider $historyProvider,
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
    public function render(): array
    {
        return $this->historyProvider->getHistory();
    }
}
