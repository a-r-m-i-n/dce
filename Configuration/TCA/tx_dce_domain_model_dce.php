<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */

$ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:';
$csh = 'LLL:EXT:dce/Resources/Private/Language/locallang_csh_dce.xlf:';

$showItems = <<<TEXT
--palette--;;general_header,fields,

--div--;{$ll}tx_dce_domain_model_dce.template,
    template_type,template_content,template_file,

--div--;{$ll}tx_dce_domain_model_dce.container,
    enable_container,container_item_limit,container_detail_autohide,
    container_identifier,container_template_type,container_template,container_template_file,

--div--;{$ll}tx_dce_domain_model_dce.detailpage,
    enable_detailpage,detailpage_identifier,
    --palette--;{$ll}tx_dce_domain_model_dce.detailpageSlugPalette;detailpage_slug,
    --palette--;{$ll}tx_dce_domain_model_dce.detailpageTitlePalette;detailpage_title,
    detailpage_template_type,detailpage_template,detailpage_template_file,

--div--;{$ll}tx_dce_domain_model_dce.backendTemplate,
    use_simple_backend_view,--palette--;;backend_view_header_settings,backend_view_bodytext,
    backend_template_type,backend_template_content,backend_template_file,

--div--;{$ll}tx_dce_domain_model_dce.wizard,
    wizard_enable,wizard_category,wizard_description,wizard_icon,wizard_custom_icon,

--div--;{$ll}tx_dce_domain_model_dce.miscellaneous,
    --palette--;;misc,flexform_label,
    --palette--;{$ll}tx_dce_domain_model_dce.contentRelationsPalette;content_relations,
    palette_fields,prevent_header_copy_suffix,template_layout_root_path,template_partial_root_path
TEXT;

$dceTca = [
    'ctrl' => [
        'title' => $ll . 'tx_dce_domain_model_dce',
        'label' => 'title',
        'label_userFunc' => 'T3\Dce\UserFunction\CustomLabels\DceFieldLabel->getLabelDce',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'dce-ext',
        ],
        'copyAfterDuplFields' => 'fields',
    ],
    'types' => [
        1 => [
            'showitem' => $showItems,
            'columnsOverrides' => [
                'template_content' => [
                    'config' => [
                        'fixedFont' => true,
                        'enableTabulator' => true
                    ]
                ],
            ],
        ]
    ],
    'palettes' => [
        'general_header' => [
            'showitem' => 'title,identifier,hidden',
        ],
        'content_relations' => [
            'showitem' => 'show_access_tab,show_media_tab,show_category_tab',
        ],
        'misc' => [
            'showitem' => 'cache_dce,direct_output',
        ],
        'detailpage_slug' => [
            'showitem' => 'detailpage_slug_expression',
        ],
        'detailpage_title' => [
            'showitem' => 'detailpage_title_expression,detailpage_use_slug_as_title',
        ],
        'backend_view_header_settings' => [
            'showitem' => 'backend_view_header,backend_view_header_expression,backend_view_header_use_expression',
        ],
    ],
    'columns' => [
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
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'identifier' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.identifier',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,unique,lower',
                'placeholder' => 'dceuidX'
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
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 1,
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
                    [
                        'group' => 'dce',
                        'label' => $ll . 'tx_dce_domain_model_dce_long',
                        'value' => 'dce',
                    ],
                    [
                        'group' => 'typo3',
                        'label' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common',
                        'value' => 'common',
                    ],
                    [
                        'group' => 'typo3',
                        'label' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special',
                        'value' => 'special',
                    ],
                    [
                        'group' => 'typo3',
                        'label' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms',
                        'value' => 'forms',
                    ],
                    [
                        'group' => 'typo3',
                        'label' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins',
                        'value' => 'plugins',
                    ],
                ],
                'itemGroups' => [
                    'dce' => $ll . 'tx_dce_domain_model_dce',
                    'typo3' => $ll . 'typo3_default_categories',
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
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'itemsProcFunc' => 'T3\Dce\UserFunction\ItemsProcFunc->getAvailableWizardIcons',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false
                    ]
                ]
            ],
        ],
        'wizard_custom_icon' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.wizardCustomIcon',
            'displayCond' => 'FIELD:wizard_icon:IN:custom',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
            ],
        ],
        'template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.inline',
                        'value' => 'inline',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.file',
                        'value' => 'file',
                    ],
                ],
            ],
        ],
        'template_content' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateContent',
            'displayCond' => 'FIELD:template_type:!IN:file',
            'config' => [
                'type' => 'text',
                'renderType' => 'dceCodeMirrorField',
                'size' => '30',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '<div class="dce">
    Your template goes here...
</div>
',
            ],
        ],
        'template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateFile',
            'description' => $csh . 'template_file.description',
            'displayCond' => 'FIELD:template_type:IN:file',
            'config' => [
                'type' => 'input',
                'required' => true,
                'size' => 30,
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
            'description' => $csh . 'flexform_label.description',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'default' => $ll . 'tx_dce_domain_model_dce.flexformLabel.default',
                'size' => 30
            ],
        ],

        'use_simple_backend_view' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.useSimpleBackendView',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => '1'
            ],
        ],
        'backend_view_header' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewHeader',
            'displayCond' => [
                'AND' => [
                    'FIELD:use_simple_backend_view:=:1',
                    'FIELD:backend_view_header_use_expression:!=:1'
                ]
            ],
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'T3\Dce\UserFunction\ItemsProcFunc->getDceFields',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1
            ],
        ],
        'backend_view_header_expression' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewHeaderExpression',
            'description' => $csh . 'backend_view_header_expression.description',
            'displayCond' => [
                'AND' => [
                    'FIELD:use_simple_backend_view:=:1',
                    'FIELD:backend_view_header_use_expression:=:1'
                ]
            ],
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => 'dce.getTitle()'
            ],
        ],
        'backend_view_header_use_expression' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewHeaderUseExpression',
            'displayCond' => 'FIELD:use_simple_backend_view:=:1',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => '0'
            ],
        ],
        'backend_view_bodytext' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendViewBodytext',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => 'T3\Dce\UserFunction\ItemsProcFunc->getDceFields',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 15
            ],
            'displayCond' => 'FIELD:use_simple_backend_view:=:1',
        ],

        'backend_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateType',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateType.inline',
                        'value'=> 'inline',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.backendTemplateType.file',
                        'value'=> 'file',
                    ],
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
                'type' => 'text',
                'renderType' => 'dceCodeMirrorField',
                'size' => '30',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '<f:layout name="BackendTemplate" />

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
            'description' => $csh . 'template_file.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:use_simple_backend_view:!=:1',
                    'FIELD:backend_template_type:IN:file'
                ]
            ],
        ],
        'template_layout_root_path' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.layoutRootPath',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'template_partial_root_path' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.partialRootPath',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'enable_detailpage' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.enableDetailpage',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
            ],
        ],
        'detailpage_identifier' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageIdentifier',
            'description' => $csh . 'detailpage_identifier.description',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,is_in',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_-',
                'default' => 'detailDceUid',
            ],
        ],
        'detailpage_slug_expression' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageSlugExpression',
            'description' => $csh . 'detailpage_slug_expression.description',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => 'contentObject[\'uid\']'
            ],
        ],
        'detailpage_title_expression' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTitleExpression',
            'description' => $csh . 'detailpage_title_expression.description',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => ''
            ],
        ],
        'detailpage_use_slug_as_title' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.useSlugAsTitle',
            'description' => $csh . 'detailpage_use_slug_as_title.description',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.useSlugAsTitle.no',
                        'value' => '',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.useSlugAsTitle.overwrite',
                        'value' => 'overwrite',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.useSlugAsTitle.prepend',
                        'value' => 'prepend',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.useSlugAsTitle.append',
                        'value' => 'append',
                    ],
                ],
            ],
        ],
        'detailpage_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'displayCond' => 'FIELD:enable_detailpage:=:1',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.inline',
                        'value' => 'inline',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.file',
                        'value' => 'file',
                    ],
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
                'type' => 'text',
                'renderType' => 'dceCodeMirrorField',
                'size' => '30',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'showTemplates' => false,
                ],
                'default' => '<div class="dce dce-detailpage">
    Your detailpage template goes here...
</div>
',
            ],
        ],
        'detailpage_template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.detailpageTemplateFile',
            'description' => $csh . 'template_file.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_detailpage:=:1',
                    'FIELD:detailpage_template_type:IN:file'
                ]
            ],
        ],
        'enable_container' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.enableContainer',
            'onChange' => 'reload',
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
        'container_detail_autohide' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.containerDetailAutohide',
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_container:=:1',
                    'FIELD:enable_detailpage:=:1'
                ]
            ],
            'config' => [
                'type' => 'check',
                'default' => '1'
            ],
        ],
        'container_template_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.templateType',
            'displayCond' => 'FIELD:enable_container:=:1',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.inline',
                        'value' => 'inline',
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dce.templateType.file',
                        'value' => 'file',
                    ],
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
                'type' => 'text',
                'renderType' => 'dceCodeMirrorField',
                'size' => '30',
                'parameters' => [
                    'mode' => 'htmlmixed',
                    'doNotShowFields' => true,
                ],
                'default' => '<f:layout name="DefaultContainer" />

<f:section name="main">
    <div class="dce-container">
        <f:render partial="Container/Dces" arguments="{dces:dces}" />
    </div>
</f:section>',
            ],
        ],
        'container_template_file' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.containerTemplateFile',
            'description' => $csh . 'template_file.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:enable_container:=:1',
                    'FIELD:container_template_type:IN:file'
                ]
            ],
        ],
        'palette_fields' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.paletteFields',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => T3\Dce\UserFunction\ItemsProcFunc::class .
                                    '->getAvailableTtContentColumnsForPaletteFields',
                'size' => 10,
                'default' => 'sys_language_uid,l18n_parent,colPos,spaceBefore,spaceAfter,section_frame,hidden',
                'minitems' => 0,
                'maxitems' => 999
            ],
        ],
        'prevent_header_copy_suffix' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.preventHeaderCopySuffix',
            'config' => [
                'type' => 'check',
                'default' => 1
            ],
        ],
        'direct_output' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dce.directOutput',
            'description' => $csh . 'direct_output.description',
            'config' => [
                'type' => 'check',
                'default' => 1
            ],
        ],
    ],
];

/** @var \TYPO3\CMS\Core\Information\Typo3Version $versionInformation */
$versionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
if ($versionInformation->getMajorVersion() >= 13) {
    // Disable "prevent_header_copy_suffix" for v13 and higher.
    $dceTca['columns']['prevent_header_copy_suffix']['config'] = ['type' => 'passthrough'];
}

if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
    $dceTca['palettes']['content_relations']['showitem'] = 'show_access_tab,show_category_tab';
    $dceTca['columns']['direct_output']['config']['readOnly'] = true;
}

return $dceTca;
