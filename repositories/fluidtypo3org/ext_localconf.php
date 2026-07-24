<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    'page.shortcutIcon = EXT:fluidtypo3org/Resources/Public/Images/favicon.ico',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'fluidtypo3org',
    'DocNavigation',
    [
        \FluidTYPO3\FluidTYPO3Org\Controller\DocumentationController::class => 'navigation',
    ],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'fluidtypo3org',
    'DocDetail',
    [
        \FluidTYPO3\FluidTYPO3Org\Controller\DocumentationController::class => 'detail',
    ],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['SafeDocumentationSegmentMapper'] =
    \FluidTYPO3\FluidTYPO3Org\Integration\Routing\SafeDocumentationSegmentMapper::class;

\FluidTYPO3\Flux\Core::registerProviderExtensionKey('FluidTYPO3.Fluidtypo3org', 'Page');
