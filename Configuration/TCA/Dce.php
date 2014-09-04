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
		'0' => array('showitem' => 'hidden;;1,type,title,fields,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.template,template_type,template_content;;;fixed-font:enable-tab,template_file,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizard,wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.detailpage,enable_detailpage,detailpage_identifier,detailpage_template_type,detailpage_template,detailpage_template_file,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.miscellaneous,cache_dce,preview_template_type,header_preview,header_preview_template_file,bodytext_preview,bodytext_preview_template_file,hide_default_ce_wrap,show_access_tab,show_category_tab,palette_fields,template_layout_root_path,template_partial_root_path,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.preview,dce_preview'),
		'1' => array('showitem' => 'hidden;;1,type,identifier,title,fields,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.template,template_type,template_content;;;fixed-font:enable-tab,template_file,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.wizard,wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.detailpage,enable_detailpage,detailpage_identifier,detailpage_template_type,detailpage_template,detailpage_template_file,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.miscellaneous,cache_dce,preview_template_type,header_preview,header_preview_template_file,bodytext_preview,bodytext_preview_template_file,hide_default_ce_wrap,show_access_tab,show_category_tab,palette_fields,template_layout_root_path,template_partial_root_path,--div--;LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.preview,dce_preview'),
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
		'type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.type.databased', 0),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.type.filebased', 1),
				),
			),
		),
		'identifier' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.identifier',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required',
				'default' => 'dce_'
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required',
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
					'levelLinksPosition' => 'both',
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
					array('LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_title', 'div', 'c_wiz/div.gif'),
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
				'allowed' => 'gif,png',
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
<f:layout name="Default" />

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
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
		'show_category_tab' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.showCategoryTab',
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
		'preview_template_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.previewTemplateType',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateType.inline', 'inline'),
					array('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.templateType.file', 'file'),
				),
			),
		),
		'header_preview' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.headerPreview',
			'displayCond' => 'FIELD:preview_template_type:!IN:file',
			'config' => array(
				'type' => 'user',
				'size' => '30',
				'userFunc' => 'EXT:dce/Classes/UserFunction/class.tx_dce_codemirrorField.php:tx_dce_codemirrorField->getCodemirrorField',
				'parameters' => array(
					'mode' => 'htmlmixed',
					'showTemplates' => FALSE,
				),
				'default' => '
{namespace dce=Tx_Dce_ViewHelpers}
{fields -> dce:arrayGetIndex()}
',
			),
		),
		'header_preview_template_file' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.headerPreviewTemplateFile',
			'displayCond' => 'FIELD:preview_template_type:IN:file',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
		'bodytext_preview' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.bodytextPreview',
			'displayCond' => 'FIELD:preview_template_type:!IN:file',
			'config' => array(
				'type' => 'user',
				'size' => '30',
				'userFunc' => 'EXT:dce/Classes/UserFunction/class.tx_dce_codemirrorField.php:tx_dce_codemirrorField->getCodemirrorField',
				'parameters' => array(
					'mode' => 'htmlmixed',
					'showTemplates' => FALSE,
				),
				'default' => '
<f:render partial="BodytextPreview/ShowAllFieldsButFirst" arguments="{fields:fields}" />
',
			),
		),
		'bodytext_preview_template_file' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.bodytextPreviewTemplateFile',
			'displayCond' => 'FIELD:preview_template_type:IN:file',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
		'enable_detailpage' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.enableDetailpage',
			'config' => array(
				'type' => 'check',
			),
		),
		'detailpage_identifier' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.detailpageIdentifier',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,is_in',
				'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_-',
				'default' => 'detailDceUid',
			),
		),
		'detailpage_template_type' => array(
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
		'detailpage_template' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.detailpageTemplate',
			'displayCond' => 'FIELD:detailpage_template_type:!IN:file',
			'config' => array (
				'type' => 'user',
				'size' => '30',
				'userFunc' => 'EXT:dce/Classes/UserFunction/class.tx_dce_codemirrorField.php:tx_dce_codemirrorField->getCodemirrorField',
				'parameters' => array(
					'mode' => 'htmlmixed',
					'showTemplates' => FALSE,
				),
				'default' => '{namespace dce=Tx_Dce_ViewHelpers}
<f:layout name="Default" />

<f:section name="main">
	Your detailpage template goes here...
</f:section>',
			),
		),
		'detailpage_template_file' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.detailpageTemplateFile',
			'displayCond' => 'FIELD:detailpage_template_type:IN:file',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					0 => array(
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
		'palette_fields' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.paletteFields',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim,is_in',
				'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890-_, ',
				'default' => 'sys_language_uid, l18n_parent, colPos, spaceBefore, spaceAfter, section_frame, sectionIndex',
			),
		),
		'dce_preview' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.dcePreview',
			'config' => array(
				'type' => 'user',
				'userFunc' => 'EXT:dce/Classes/UserFunction/class.tx_dce_dcePreviewField.php:tx_dce_dcePreviewField->getPreview',
			),
		),
	),
);

if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
	unset($TCA['tx_dce_domain_model_dce']['columns']['show_category_tab']);
}