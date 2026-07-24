<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$navigationSignature = ExtensionUtility::registerPlugin(
    'fluidtypo3org',
    'DocNavigation',
    'Documentation: Navigation',
    'content-plugin',
    'plugins',
    'Hierarchical navigation for the bundled Markdown documentation.',
    'FILE:EXT:fluidtypo3org/Configuration/FlexForms/DocumentationNavigation.xml',
);
$GLOBALS['TCA']['tt_content']['types'][$navigationSignature]['showitem'] = '
    --palette--;;headers,
    pi_flexform,
';

$detailSignature = ExtensionUtility::registerPlugin(
    'fluidtypo3org',
    'DocDetail',
    'Documentation: Detail',
    'content-plugin',
    'plugins',
    'Folder overview and GitHub-flavored Markdown detail view.',
    'FILE:EXT:fluidtypo3org/Configuration/FlexForms/DocumentationDetail.xml',
);
$GLOBALS['TCA']['tt_content']['types'][$detailSignature]['showitem'] = '
    --palette--;;headers,
    pi_flexform,
';
