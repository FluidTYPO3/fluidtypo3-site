<?php
return array (
	'ctrl' =>
		array (
			'title' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist',
			'label' => 'title',
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
			'showRecordFieldList' => 'confirmed,extensions,summary,tags,title,slug,url',
		),
	'columns' =>
		array (
			'confirmed' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.confirmed',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'check',
							'transform' => NULL,
							'default' => NULL,
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
			'extensions' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.extensions',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'select',
							'transform' => NULL,
							'default' => NULL,
							'size' => 5,
							'maxitems' => 99,
							'minitems' => 0,
							'multiple' => false,
							'renderMode' => 'default',
							'itemListStyle' => NULL,
							'selectedListStyle' => NULL,
							'foreign_table' => 'tx_fluidshare_domain_model_extension',
							'foreign_field' => NULL,
							'foreign_table_where' => NULL,
							'foreign_table_field' => NULL,
							'foreign_unique' => NULL,
							'foreign_label' => NULL,
							'foreign_selector' => NULL,
							'foreign_sortby' => NULL,
							'foreign_default_sortby' => NULL,
							'symmetricSortBy' => NULL,
							'symmetricLabel' => NULL,
							'symmetricField' => NULL,
							'localizationMode' => NULL,
							'localizeChildrenAtParentLocalization' => 0,
							'disableMovingChildrenWithParent' => 0,
							'showThumbs' => 0,
							'MM' => 'tx_fluidshare_gist_extension_mm',
							'filter' =>
								array (
								),
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
			'summary' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.summary',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'text',
							'transform' => NULL,
							'default' => NULL,
							'rows' => 10,
							'cols' => 85,
							'eval' => NULL,
							'defaultExtras' => NULL,
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
			'tags' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.tags',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'select',
							'transform' => NULL,
							'default' => NULL,
							'size' => 5,
							'maxitems' => 99,
							'minitems' => 0,
							'multiple' => false,
							'renderMode' => 'default',
							'itemListStyle' => NULL,
							'selectedListStyle' => NULL,
							'foreign_table' => 'tx_fluidshare_domain_model_tag',
							'foreign_field' => NULL,
							'foreign_table_where' => NULL,
							'foreign_table_field' => NULL,
							'foreign_unique' => NULL,
							'foreign_label' => NULL,
							'foreign_selector' => NULL,
							'foreign_sortby' => NULL,
							'foreign_default_sortby' => NULL,
							'symmetricSortBy' => NULL,
							'symmetricLabel' => NULL,
							'symmetricField' => NULL,
							'localizationMode' => NULL,
							'localizeChildrenAtParentLocalization' => 0,
							'disableMovingChildrenWithParent' => 0,
							'showThumbs' => 0,
							'MM' => 'tx_fluidshare_gist_tag_mm',
							'filter' =>
								array (
								),
							'wizards' =>
								array (
								),
						),
					'displayCond' => NULL,
				),
			'title' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.title',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'input',
							'transform' => NULL,
							'default' => NULL,
							'placeholder' => NULL,
							'size' => 100,
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
			'slug' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.slug',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'slug',
							'size' => 100,
							'generatorOptions' =>
								array (
									'fields' =>
										array (
											'title',
										),
									'fieldSeparator' => '-',
									'prefixParentPageSlug' => false,
								),
							'fallbackCharacter' => '-',
							'eval' => 'uniqueInSite',
							'default' => '',
						),
				),
			'url' =>
				array (
					'label' => 'LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.fields.url',
					'exclude' => 1,
					'config' =>
						array (
							'type' => 'input',
							'transform' => NULL,
							'default' => NULL,
							'placeholder' => NULL,
							'size' => 100,
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
					'showitem' => '--div--;LLL:EXT:fluidshare/Resources/Private/Language/locallang.xlf:flux.tx_fluidshare_domain_model_gist.sheets.options, confirmed, extensions, summary, tags, title, slug, url',
				),
		),
);
