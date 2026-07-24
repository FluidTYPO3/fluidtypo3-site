<?php
return array (
	'ctrl' =>
		array (
			'title' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_tag',
			'label' => 'name',
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
			'showRecordFieldList' => 'name',
		),
	'columns' =>
		array (
			'name' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_tag.fields.name',
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
					'showitem' => '--div--;LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_tag.sheets.options, name',
				),
		),
);
