<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin('Fluidshare', 'Display', 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:plugin.display');
ExtensionUtility::registerPlugin('Fluidshare', 'Detail', 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:plugin.detail');
ExtensionUtility::registerPlugin('Fluidshare', 'Filter', 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:plugin.filter');
ExtensionUtility::registerPlugin('Fluidshare', 'Submit', 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:plugin.submit');
