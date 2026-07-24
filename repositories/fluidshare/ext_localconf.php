<?php

declare(strict_types=1);

use FluidTYPO3\Fluidshare\Controller\GistController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
	'Fluidshare',
	'Display',
	[GistController::class => 'list'],
	[GistController::class => 'list'],
);

ExtensionUtility::configurePlugin(
	'Fluidshare',
	'Detail',
	[GistController::class => 'display'],
);

ExtensionUtility::configurePlugin(
	'Fluidshare',
	'Filter',
	[GistController::class => 'list'],
	[GistController::class => 'list'],
);

ExtensionUtility::configurePlugin(
	'Fluidshare',
	'Submit',
	[GistController::class => 'new,confirm,create'],
	[GistController::class => 'confirm,create'],
);
