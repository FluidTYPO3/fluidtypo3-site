<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('Fluidshare', 'Display',
	[\FluidTYPO3\Fluidshare\Controller\GistController::class => 'list,display'],
	[\FluidTYPO3\Fluidshare\Controller\GistController::class => 'list']
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	'Fluidshare',
	'setup',
	'
tt_content.fluidshare_filter =< lib.contentElement
tt_content.fluidshare_filter {
	templateName = Generic
	20 = EXTBASEPLUGIN
	20 {
		extensionName = Fluidshare
		pluginName = Display
	}
}
',
	'defaultContentRendering'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('Fluidshare', 'Submit',
	[\FluidTYPO3\Fluidshare\Controller\GistController::class => 'new,confirm,create'],
	[\FluidTYPO3\Fluidshare\Controller\GistController::class => 'confirm,create']
);
