<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:';
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('dce');

$dceTca = array(
    'ctrl' => array(
        'title' => $ll . 'tx_dce_domain_model_dce',
        'label' => 'title',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'versioningWS' => 2,
        'versioning_followPages' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'copyAfterDuplFields' => 'fields',
        'requestUpdate' => 'wizard_enable,wizard_icon,template_type,preview_template_type,detailpage_template_type,enable_detailpage',
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicons' => array(
            '0' => $extensionPath . 'Resources/Public/Icons/tx_dce_domain_model_dce_databased.png',
            '1' => $extensionPath . 'Resources/Public/Icons/tx_dce_domain_model_dce_filebased.png',
        ),
        'iconfile' => $extensionPath . 'ext_icon.png'
    ),
    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden',
    ),
    'types' => array(
        // Database DCE
        '0' => array(
            'showitem' => 'hidden;;1,type,title,fields,
			--div--;' . $ll . 'tx_dce_domain_model_dce.template,template_type,template_content;;;fixed-font:enable-tab,template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.backendTemplate,preview_template_type,header_preview,header_preview_template_file,bodytext_preview,bodytext_preview_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.wizard,wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,
			--div--;' . $ll . 'tx_dce_domain_model_dce.detailpage,enable_detailpage,detailpage_identifier,detailpage_template_type,detailpage_template,detailpage_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.miscellaneous,cache_dce,hide_default_ce_wrap,show_access_tab,show_category_tab,palette_fields,template_layout_root_path,template_partial_root_path'
        ),
        // Filebased DCE
        '1' => array(
            'showitem' => 'hidden;;1,type,identifier,title,fields,
			--div--;' . $ll . 'tx_dce_domain_model_dce.template,template_content;;;fixed-font:enable-tab,template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.backendTemplate,preview_template_type,header_preview,header_preview_template_file,bodytext_preview,bodytext_preview_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.wizard,wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,
			--div--;' . $ll . 'tx_dce_domain_model_dce.detailpage,enable_detailpage,detailpage_identifier,detailpage_template,detailpage_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.miscellaneous,cache_dce,hide_default_ce_wrap,show_access_tab,show_category_tab,palette_fields,template_layout_root_path,template_partial_root_path'
        ),
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
                'foreign_table_where' => 'AND tx_dce_domain_model_dce.pid=###CURRENT_PID### ' .
                    'AND tx_dce_domain_model_dce.sys_language_uid IN (-1,0)',
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
            'label' => $ll . 'tx_dce_domain_model_dce.type',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dce.type.databased', 0),
                    array($ll . 'tx_dce_domain_model_dce.type.filebased', 1),
                ),
                'readOnly' => true,
            ),
        ),
        'identifier' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.identifier',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'default' => 'dce_'
            ),
        ),
        'title' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.title',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ),
        ),
        'fields' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.fields',
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
            'label' => $ll . 'tx_dce_domain_model_dce.wizardEnable',
            'config' => array(
                'type' => 'check',
                'default' => true,
            ),
        ),
        'wizard_category' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardCategory',
            'displayCond' => 'FIELD:wizard_enable:REQ:true',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dce', '--div--'),
                    array($ll . 'tx_dce_domain_model_dce_long', 'dce'),
                    array($ll . 'typo3_default_categories', '--div--'),
                    array('LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common', 'common'),
                    array('LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special', 'special'),
                    array('LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms', 'forms'),
                    array('LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins', 'plugins'),
                ),
            ),
        ),
        'wizard_description' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardDescription',
            'displayCond' => 'FIELD:wizard_enable:REQ:true',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'wizard_icon' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardIcon',
            'displayCond' => 'FIELD:wizard_enable:REQ:true',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'wizardIcon.default', '--div--'),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_regularText_title',
                        'regular_text',
                        'c_wiz/regular_text.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_plainHTML_title',
                        'html',
                        'c_wiz/html.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_bulletList_title',
                        'bullet_list',
                        'c_wiz/bullet_list.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_textImage_title',
                        'text_image_right',
                        'c_wiz/text_image_right.gif'
                    ),
                    array($ll . 'common_textImage2_title', 'text_image_below', 'c_wiz/text_image_below.gif'),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_table_title',
                        'table',
                        'c_wiz/table.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_sitemap_title',
                        'sitemap',
                        'c_wiz/sitemap.gif'
                    ),
                    array($ll . 'special_sitemap2_title', 'sitemap2', 'c_wiz/sitemap2.gif'),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_search_title',
                        'searchform',
                        'c_wiz/searchform.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_multimedia_title',
                        'multimedia',
                        'c_wiz/multimedia.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_mail_title',
                        'mailform',
                        'c_wiz/mailform.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_login_title',
                        'login_form',
                        'c_wiz/login_form.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_imagesOnly_title',
                        'images_only',
                        'c_wiz/images_only.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_filelinks_title',
                        'filelinks',
                        'c_wiz/filelinks.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_title',
                        'div',
                        'c_wiz/div.gif'
                    ),
                    array(
                        'LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins_general_title',
                        'user_defined',
                        'c_wiz/user_defined.gif'
                    ),
                    array($ll . 'wizardIcon.custom', '--div--'),
                    array($ll . 'wizardIcon.customIcon', 'custom'),
                ),
            ),
        ),
        'wizard_custom_icon' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardCustomIcon',
            'displayCond' => 'FIELD:wizard_icon:IN:custom',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file',
                'uploadfolder' => 'uploads/tx_dce',
                'show_thumbs' => 1,
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'allowed' => 'gif,png',
                'disallowed' => '',
            ),
        ),
        'template_type' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'),
                    array($ll . 'tx_dce_domain_model_dce.templateType.file', 'file'),
                ),
            ),
        ),
        'template_content' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateContent',
            'displayCond' => 'FIELD:template_type:!IN:file',
            'config' => array(
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'EXT:dce/Classes/UserFunction/UserFields/CodemirrorField.php:' .
                    'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => array(
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ),
                'default' => '{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="Default" />

<f:section name="main">
	Your template goes here...
</f:section>',
            ),
        ),
        'template_file' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateFile',
            'displayCond' => 'FIELD:template_type:IN:file',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ),
        ),
        'cache_dce' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.cacheDce',
            'config' => array(
                'type' => 'check',
                'default' => '1',
            ),
        ),
        'show_access_tab' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.showAccessTab',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            ),
        ),
        'show_category_tab' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.showCategoryTab',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            ),
        ),
        'hide_default_ce_wrap' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.hideDefaultCeWrap',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            ),
        ),
        'preview_template_type' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.previewTemplateType',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'),
                    array($ll . 'tx_dce_domain_model_dce.templateType.file', 'file'),
                ),
            ),
        ),
        'header_preview' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.headerPreview',
            'displayCond' => 'FIELD:preview_template_type:!IN:file',
            'config' => array(
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'EXT:dce/Classes/UserFunction/UserFields/CodemirrorField.php:' .
                    'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => array(
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ),
                'default' => '
{namespace dce=ArminVieweg\Dce\ViewHelpers}
{fields -> dce:arrayGetIndex()}
',
            ),
        ),
        'header_preview_template_file' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.headerPreviewTemplateFile',
            'displayCond' => 'FIELD:preview_template_type:IN:file',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ),
        ),
        'bodytext_preview' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.bodytextPreview',
            'displayCond' => 'FIELD:preview_template_type:!IN:file',
            'config' => array(
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'EXT:dce/Classes/UserFunction/UserFields/CodemirrorField.php:' .
                    'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => array(
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ),
                'default' => '
<f:render partial="BodytextPreview/ShowAllFieldsButFirst" arguments="{fields:fields}" />
',
            ),
        ),
        'bodytext_preview_template_file' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.bodytextPreviewTemplateFile',
            'displayCond' => 'FIELD:preview_template_type:IN:file',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ),
        ),
        'template_layout_root_path' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.layoutRootPath',
            'config' => array(
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => 'EXT:dce/Resources/Private/Layouts/',
            ),
        ),
        'template_partial_root_path' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.partialRootPath',
            'config' => array(
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => 'EXT:dce/Resources/Private/Partials/',
            ),
        ),
        'enable_detailpage' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.enableDetailpage',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'detailpage_identifier' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageIdentifier',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
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
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'),
                    array($ll . 'tx_dce_domain_model_dce.templateType.file', 'file'),
                ),
            ),
        ),
        'detailpage_template' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTemplate',
            'displayCond' => array(
                'AND' => array(
                    'FIELD:enable_detailpage:=:1',
                    'FIELD:detailpage_template_type:!IN:file'
                )
            ),
            'config' => array(
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'EXT:dce/Classes/UserFunction/UserFields/CodemirrorField.php:' .
                    'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => array(
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ),
                'default' => '{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="Default" />

<f:section name="main">
	Your detailpage template goes here...
</f:section>',
            ),
        ),
        'detailpage_template_file' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTemplateFile',
            'displayCond' => array(
                'AND' => array(
                    'FIELD:enable_detailpage:=:1',
                    'FIELD:detailpage_template_type:IN:file'
                )
            ),
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ),
        ),
        'palette_fields' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.paletteFields',
            'config' => array(
                'type' => 'input',
                'eval' => 'trim,is_in',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890-_, ',
                'default' => 'sys_language_uid, l18n_parent, colPos, spaceBefore, spaceAfter, section_frame, hidden',
            ),
        ),
    ),
);


if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.4')) {
    $dceTca['columns']['wizard_icon']['config']['items'] = array(
        array($ll . 'wizardIcon.default', '--div--'),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_regularText_title',
            'regular_text',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/regular_header.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_plainHTML_title',
            'html',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/html.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_bulletList_title',
            'bullet_list',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/bullet_list.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textImage_title',
            'text_image_right',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/text_image_right.gif'
        ),
        array(
            $ll . 'common_textImage2_title',
            'text_image_below',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/text_image_below.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_table_title',
            'table',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/table.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_sitemap_title',
            'sitemap',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/sitemap.gif'
        ),
        array(
            $ll . 'special_sitemap2_title',
            'sitemap2',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/sitemap2.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms_search_title',
            'searchform',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/searchform.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_multimedia_title',
            'multimedia',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/multimedia.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms_mail_title',
            'mailform',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/mailform.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms_login_title',
            'login_form',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/login_form.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_imagesOnly_title',
            'images_only',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/images_only.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_filelinks_title',
            'filelinks',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/filelinks.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_divider_title',
            'div',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/div.gif'
        ),
        array(
            'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins_general_title',
            'user_defined',
            'EXT:frontend/Resources/Public/Icons/ContentElementWizard/user_defined.gif'
        ),
        array($ll . 'wizardIcon.custom', '--div--'),
        array($ll . 'wizardIcon.customIcon', 'custom'),
    );
}

return $dceTca;
