<?php

declare(strict_types=1);

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'Donation',
        'label' => 'donation_date',
        'label_alt' => 'amount',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'donation_date DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'amount',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'Visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'donation_date' => [
            'label' => 'Timestamp',
            'description' => 'Date and time when the donation or withdrawal was recorded.',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'required' => true,
                'default' => 0,
            ],
        ],
        'amount' => [
            'label' => 'Amount in EUR',
            'description' => 'Use a positive amount for received funds and a negative amount for withdrawn funds.',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'required' => true,
                'default' => 0.0,
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                donation_date,
                amount,
                --div--;Access,
                    hidden
            ',
        ],
    ],
];
