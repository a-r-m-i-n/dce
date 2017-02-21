<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:';

$dceTca = [
    'ctrl' => [
        'title' => $ll . 'tx_dce_domain_model_dce',
        'label' => 'title',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:dce/Resources/Public/Icons/ext_icon.png',
        'copyAfterDuplFields' => 'fields',
        'requestUpdate' => 'wizard_enable,wizard_icon,template_type,backend_template_type,use_simple_backend_view,' .
                           'backend_view_header,enable_detailpage,detailpage_template_type,' .
                           'enable_container,container_template_type',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,type',
    ],
    'types' => [
        1 => [
            'showitem' => '--palette--;;general_header,fields,
			--div--;' . $ll . 'tx_dce_domain_model_dce.template,template_type,template_content;;;fixed-font:enable-tab,template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.container,enable_container,container_item_limit,container_identifier,container_template_type,container_template,container_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.backendTemplate,use_simple_backend_view,backend_view_header,backend_view_bodytext,backend_template_type,backend_template_content,backend_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.wizard,wizard_icon,wizard_custom_icon,wizard_enable,wizard_category,wizard_description,
			--div--;' . $ll . 'tx_dce_domain_model_dce.detailpage,enable_detailpage,detailpage_identifier,detailpage_template_type,detailpage_template,detailpage_template_file,
			--div--;' . $ll . 'tx_dce_domain_model_dce.miscellaneous,cache_dce,flexform_label,hide_default_ce_wrap,
                --palette--;' . $ll . 'tx_dce_domain_model_dce.contentRelationsPalette;content_relations,palette_fields,template_layout_root_path,template_partial_root_path'
        ]
    ],
    'palettes' => [
        'general_header' => ['showitem' => 'title,hidden', 'canNotCollapse' => true],
        'content_relations' => ['showitem' => 'show_access_tab,show_media_tab,show_category_tab', 'canNotCollapse' => true],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_dce_domain_model_dce',
                'foreign_table_where' => 'AND tx_dce_domain_model_dce.pid=###CURRENT_PID### ' .
                    'AND tx_dce_domain_model_dce.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => $ll . 'tx_dce_domain_model_dce.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.title',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,required',
            ],
        ],
        'fields' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.fields',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'foreign_sortby' => 'sorting',
                'foreign_field' => 'parent_dce',
                'minitems' => 0,
                'maxitems' => 999,
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'levelLinksPosition' => 'bottom',
                    'useSortable' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showRemovedLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                    'showSynchronizationLink' => 1,
                    'enabledControls' => [
                        'info' => false,
                        'dragdrop' => true,
                        'sort' => true
                    ]
                ],
            ],
        ],
        'wizard_enable' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardEnable',
            'config' => [
                'type' => 'check',
                'default' => true,
            ],
        ],
        'wizard_category' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardCategory',
            'displayCond' => 'FIELD:wizard_enable:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dce', '--div--'],
                    [$ll . 'tx_dce_domain_model_dce_long', 'dce'],
                    [$ll . 'typo3_default_categories', '--div--'],
                    ['LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common', 'common'],
                    ['LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special', 'special'],
                    ['LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms', 'forms'],
                    ['LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins', 'plugins'],
                ],
            ],
        ],
        'wizard_description' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardDescription',
            'displayCond' => 'FIELD:wizard_enable:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'wizard_icon' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardIcon',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'items' => \ArminVieweg\Dce\Utility\WizardIcon::getTcaListItems(),
                'showIconTable' => true
            ],
        ],
        'wizard_custom_icon' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardCustomIcon',
            'displayCond' => 'FIELD:wizard_icon:IN:custom',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'gif,png,svg,jpg',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'show_thumbs' => true
            ],
        ],
        'template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'],
                    [$ll . 'tx_dce_domain_model_dce.templateType.file', 'file'],
                ],
            ],
        ],
        'template_content' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateContent',
            'displayCond' => 'FIELD:template_type:!IN:file',
            'config' => [
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="Default" />

<f:section name="main">
	Your template goes here...
</f:section>',
            ],
        ],
        'template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateFile',
            'displayCond' => 'FIELD:template_type:IN:file',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'cache_dce' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.cacheDce',
            'config' => [
                'type' => 'check',
                'default' => '1',
            ],
        ],
        'show_access_tab' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.showAccessTab',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'show_category_tab' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.showCategoryTab',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'show_media_tab' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.showMediaTab',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'flexform_label' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.flexformLabel',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => $ll . 'tx_dce_domain_model_dce.flexformLabel.default',
                'size' => 30
            ],
        ],

        'use_simple_backend_view' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.useSimpleBackendView',
            'config' => [
                'type' => 'check',
                'default' => '1'
            ],
        ],
        'backend_view_header' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewHeader',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'ArminVieweg\Dce\UserFunction\ItemsProcFunc->getDceFields',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1
            ],
            'displayCond' => 'FIELD:use_simple_backend_view:=:1',
        ],
        'backend_view_bodytext' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewBodytext',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'ArminVieweg\Dce\UserFunction\ItemsProcFunc->getDceFields',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 15
            ],
            'displayCond' => 'FIELD:use_simple_backend_view:=:1',
        ],

        'backend_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dce.backendTemplateType.inline', 'inline'],
                    [$ll . 'tx_dce_domain_model_dce.backendTemplateType.file', 'file'],
                ],
            ],
            'displayCond' => 'FIELD:use_simple_backend_view:!=:1',
        ],
        'backend_template_content' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateContent',
            'displayCond' => [
                'AND' => [
                    'FIELD:use_simple_backend_view:!=:1',
                    'FIELD:backend_template_type:!IN:file'
                ]
            ],
            'config' => [
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '
{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="BackendTemplate" />

<f:section name="header">
	<strong>{dce.title}</strong><br>
</f:section>
<f:section name="bodytext">
	Your backend template goes here...
</f:section>',
            ],
        ],
        'backend_template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateFile',
            'displayCond' => [
                'AND' => [
                    'FIELD:use_simple_backend_view:!=:1',
                    'FIELD:backend_template_type:IN:file'
                ]
            ],
            'config' => [
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'template_layout_root_path' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.layoutRootPath',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => 'EXT:dce/Resources/Private/Layouts/',
            ],
        ],
        'template_partial_root_path' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.partialRootPath',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required',
                'default' => 'EXT:dce/Resources/Private/Partials/',
            ],
        ],
        'enable_detailpage' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.enableDetailpage',
            'config' => [
                'type' => 'check',
            ],
        ],
        'detailpage_identifier' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageIdentifier',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,is_in',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_-',
                'default' => 'detailDceUid',
            ],
        ],
        'detailpage_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'],
                    [$ll . 'tx_dce_domain_model_dce.templateType.file', 'file'],
                ],
            ],
        ],
        'detailpage_template' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTemplate',
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_detailpage:=:1',
                    'FIELD:detailpage_template_type:!IN:file'
                ]
            ],
            'config' => [
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="Default" />

<f:section name="main">
	Your detailpage template goes here...
</f:section>',
            ],
        ],
        'detailpage_template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTemplateFile',
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_detailpage:=:1',
                    'FIELD:detailpage_template_type:IN:file'
                ]
            ],
            'config' => [
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'enable_container' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.enableContainer',
            'config' => [
                'type' => 'check',
            ],
        ],
        'container_item_limit' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.containerItemLimit',
            'displayCond' => 'FIELD:enable_container:=:1',
            'config' => [
                'type' => 'input',
                'eval' => 'num',
                'default' => 0,
                'size' => 2,
            ],
        ],
        'container_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'displayCond' => 'FIELD:enable_container:=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dce.templateType.inline', 'inline'],
                    [$ll . 'tx_dce_domain_model_dce.templateType.file', 'file'],
                ],
            ],
        ],
        'container_template' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.containerTemplate',
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_container:=:1',
                    'FIELD:container_template_type:!IN:file'
                ]
            ],
            'config' => [
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'doNotShowFields' => true,
                ],
                'default' => '{namespace dce=ArminVieweg\Dce\ViewHelpers}
<f:layout name="DefaultContainer" />

<f:section name="main">
	<f:render partial="Container/Dces" arguments="{dces:dces}" />
</f:section>',
            ],
        ],
        'container_template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.containerTemplateFile',
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_container:=:1',
                    'FIELD:container_template_type:IN:file'
                ]
            ],
            'config' => [
                'type' => 'group',
                'internal_type' => 'file_reference',
                'allowed' => 'html,htm',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'palette_fields' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.paletteFields',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'ArminVieweg\Dce\UserFunction\ItemsProcFunc->getAvailableTtContentColumnsForPaletteFields',
                'size' => 10,
                'default' => 'sys_language_uid,l18n_parent,colPos,spaceBefore,spaceAfter,section_frame,hidden',
                'minitems' => 0,
                'maxitems' => 999
            ],
        ],
    ],
];

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('css_styled_content')) {
    //hide_default_ce_wrap
    $dceTca['columns']['hide_default_ce_wrap'] = [
        'exclude' => 0,
        'label' => $ll . 'tx_dce_domain_model_dce.hideDefaultCeWrap',
        'config' => [
            'type' => 'check',
            'default' => '0',
        ]
    ];
}
if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
    $dceTca['palettes']['content_relations']['showitem'] = 'show_access_tab,show_category_tab';
}

return $dceTca;
