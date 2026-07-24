<?php

return array (
	'ctrl' =>
		array (
			'title' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_extension',
			'label' => 'extension_key',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'dividers2tabs' => true,
			'enablecolumns' =>
				array (
					'disabled' => 'hidden',
				),
			#'iconfile' => '../typo3conf/ext/fluidshare/ext_icon.gif',
			'hideTable' => false,
			'languageField' => 'sys_language_uid',
			'delete' => 'deleted',
			'label_alt' => '',
		),
	'interface' =>
		array (
			'showRecordFieldList' => 'extension_key,extension_name',
		),
	'columns' =>
		array (
			'extension_key' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_extension.fields.extension_key',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'input',
							'transform' => NULL,
							'default' => NULL,
							'placeholder' => NULL,
							'size' => 32,
							'max' => NULL,
							'eval' => NULL,
							'range' =>
								array (
									'lower' => NULL,
									'upper' => NULL,
								),
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
			'extension_name' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_extension.fields.extension_name',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'input',
							'transform' => NULL,
							'default' => NULL,
							'placeholder' => NULL,
							'size' => 32,
							'max' => NULL,
							'eval' => NULL,
							'range' =>
								array (
									'lower' => NULL,
									'upper' => NULL,
								),
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
		),
	'types' =>
		array (
			0 =>
				array (
					'showitem' => '--div--;LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_extension.sheets.options, extension_key, extension_name',
				),
		),
);
