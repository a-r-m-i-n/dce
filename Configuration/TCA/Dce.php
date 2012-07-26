<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_dce_domain_model_dce'] = array(
	'ctrl' => $TCA['tx_dce_domain_model_dce']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden',
	),
	'types' => array(
		'1' => array('showitem' => 'hidden;;1,title,fields,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.template,template_type,template_content;;;fixed-font:enable-tab,template_file,template_layout_root_path,template_partial_root_path,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizard,wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.miscellaneous,cache_dce,header_preview,bodytext_preview,hide_default_ce_wrap,show_access_tab'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_dce_domain_model_dce',
				'foreign_table_where' => 'AND tx_dce_domain_model_dce.pid=###CURRENT_PID### AND tx_dce_domain_model_dce.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'fields' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.fields',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_dce_domain_model_dcefield',
				'MM' => 'tx_dce_dce_dcefield_mm',
				'minitems' => 1,
				'maxitems' => 999,
				'appearance' => array(
					'enabledControls' => array(
						'hide' => false,
						'dragdrop' => true,
						'sort' => true,
					),
				),
			),
		),
		'wizard_enable' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizardEnable',
			'config' => array(
				'type' => 'check',
			),
		),
		'wizard_category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizardCategory',
			'displayCond' => 'FIELD:wizard_enable:REQ:true',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce', '--div--'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce_long', 'dce'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:typo3_default_categories', '--div--'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common', 'common'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special', 'special'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms', 'forms'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins', 'plugins'),
				),
			),
		),
		'wizard_description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizardDescription',
			'displayCond' => 'FIELD:wizard_enable:REQ:true',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'wizard_icon' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizardIcon',
			'displayCond' => 'FIELD:wizard_enable:REQ:true',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:wizardIcon.default', '--div--'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_regularText_title', 'regular_text', 'c_wiz/regular_text.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_plainHTML_title', 'html', 'c_wiz/html.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_bulletList_title', 'bullet_list', 'c_wiz/bullet_list.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_textImage_title', 'text_image_right', 'c_wiz/text_image_right.gif'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:common_textImage2_title', 'text_image_below', 'c_wiz/text_image_below.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_table_title', 'table', 'c_wiz/table.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_sitemap_title', 'sitemap', 'c_wiz/sitemap.gif'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:special_sitemap2_title', 'sitemap2', 'c_wiz/sitemap2.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_search_title', 'searchform', 'c_wiz/searchform.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_multimedia_title', 'multimedia', 'c_wiz/multimedia.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_mail_title', 'mailform', 'c_wiz/mailform.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_login_title', 'login_form', 'c_wiz/login_form.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_imagesOnly_title', 'images_only', 'c_wiz/images_only.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_filelinks_title', 'filelinks', 'c_wiz/filelinks.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_title', 'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_title', 'c_wiz/div.gif'),
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins_general_title', 'user_defined', 'c_wiz/user_defined.gif'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:wizardIcon.custom', '--div--'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:wizardIcon.customIcon', 'custom'),
				),
			),
		),
		'wizard_custom_icon' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizardCustomIcon',
			'displayCond' => 'FIELD:wizard_enable:REQ:true',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'uploadfolder' => 'uploads/tx_dce',
				'show_thumbs' => 1,
				'size' => 2,
				'minitems' => 0,
				'maxitems' => 1,
				'allowed' => 'GIF,PNG',
				'disallowed' => '',
			),
		),

		'template_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateType',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateType.inline', 'inline'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateType.file', 'file'),
				),
			),
		),
		'template_content' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateContent',
			'displayCond' => 'FIELD:template_type:!IN:file',
			'config' => array (
				'type' => 'user',
				'size' => '30',
				'userFunc' => 'EXT:dce/Classes/UserFunction/class.tx_dce_codemirrorField.php:tx_dce_codemirrorField->getCodemirrorField',
				'parameters' => array(
					'mode' => 'htmlmixed',
					'showTemplates' => FALSE,
				),
				'default' => '{namespace dce=Tx_Dce_ViewHelpers}
<f:layout name="default" />

<f:section name="main">
	Your template goes here...
</f:section>',
			),
		),
		'template_file' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateFile',
			'displayCond' => 'FIELD:template_type:IN:file',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'wizards' => Array(
					'_PADDING' => 2,
					0 => Array(
						'title' => 'Link',
						'type' => 'popup',
						'icon' => 'i/pages.gif',
						'script' => 'browse_links.php?mode=wizard',
						'params' => array(
							'blindLinkOptions' => 'page,url,mail,spec,folder',
							'allowedExtensions' => 'htm,html,tmp,tmpl',
						),
						'JSopenParams' => 'height=400,width=500,status=0,menubar=0,scrollbars=1',
					),
				),

			),
		),
		'template_layout_root_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.layoutRootPath',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim,required',
				'default' => 'EXT:dce/Resources/Private/Layouts/',
				'wizards' => Array(
					'_PADDING' => 2,
					0 => Array(
						'title' => 'Link',
						'type' => 'popup',
						'icon' => 'fileicons/folder.gif',
						'script' => 'browse_links.php?mode=wizard&amp;act=folder',
						'params' => array(
							'blindLinkOptions' => 'page,url,mail,spec,file',
						),
						'JSopenParams' => 'height=400,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'template_partial_root_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.partialRootPath',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim,required',
				'default' => 'EXT:dce/Resources/Private/Partials/',
				'wizards' => Array(
					'_PADDING' => 2,
					0 => Array(
						'title' => 'Link',
						'type' => 'popup',
						'icon' => 'fileicons/folder.gif',
						'script' => 'browse_links.php?mode=wizard&amp;act=folder',
						'params' => array(
							'blindLinkOptions' => 'page,url,mail,spec,file',
						),
						'JSopenParams' => 'height=400,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'cache_dce' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.cacheDce',
			'config' => array(
				'type' => 'check',
				'default' => '1',
			),
		),
		'show_access_tab' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.showAccessTab',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'hide_default_ce_wrap' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.hideDefaultCeWrap',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'header_preview' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.headerPreview',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'bodytext_preview' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.bodytextPreview',
			'config' => array(
				'type' => 'text',
				'rows' => 5,
				'cols' => 30,
				'eval' => 'trim'
			),
		),
	),
);
?>