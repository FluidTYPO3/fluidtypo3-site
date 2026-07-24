<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'FluidTYPO3.org',
    'description' => 'Site package for fluidtypo3.org',
    'category' => 'templates',
    'author' => 'FluidTYPO3 Team',
    'author_email' => '',
    'author_company' => 'FluidTYPO3',
    'state' => 'alpha',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'flux' => '12.0.0-12.99.99',
            'typo3' => '14.3.0-14.3.99',
            'vhs' => '8.0.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
