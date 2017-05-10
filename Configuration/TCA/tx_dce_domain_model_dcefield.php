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
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('dce');

$dceFieldTca = [
    'ctrl' => [
        'title' => $ll . 'tx_dce_domain_model_dcefield',
        'label' => 'title',
        'label_userFunc' => 'ArminVieweg\Dce\UserFunction\CustomLabels\DceFieldLabel->getLabel',
        'hideTable' => true,
        'adminOnly' => true,
        'rootLevel' => true,
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
        'requestUpdate' => 'type,map_to',
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            '0' => 'ext-dce-dcefield-type-element',
            '1' => 'ext-dce-dcefield-type-tab',
            '2' => 'ext-dce-dcefield-type-section'
        ],
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,type,variable',
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;;general_header,configuration;;;fixed-font:enable-tab,' .
                          '--palette--;;tca_options,parent_dce,parent_field'
        ],
        '1' => [
            'showitem' => '--palette--;;general_header,parent_dce'
        ],
        '2' => [
            'showitem' => '--palette--;;general_header,section_fields_tag,section_fields,parent_dce'
        ],
    ],
    'palettes' => [
        'general_header' => ['showitem' => 'type,title,variable,hidden', 'canNotCollapse' => true],
        'tca_options' => ['showitem' => 'map_to,new_tca_field_name,new_tca_field_type', 'canNotCollapse' => true]
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
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'foreign_table_where' => 'AND tx_dce_domain_model_dcefield.pid=###CURRENT_PID### ' .
                    'AND tx_dce_domain_model_dcefield.sys_language_uid IN (-1,0)',
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
            'label' => $ll . 'tx_dce_domain_model_dcefield.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'sorting' => [
            'label' => 'Sorting',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'tx_dce_domain_model_dcefield.type.element', 0],
                    [$ll . 'tx_dce_domain_model_dcefield.type.tab', 1],
                    [$ll . 'tx_dce_domain_model_dcefield.type.section', 2],
                ],
            ],
            'displayCond' => 'FIELD:parent_field:=:0'
        ],
        'title' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.title',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,required'
            ],
        ],
        'variable' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.variable',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,required,is_in,' .
                          'ArminVieweg\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator,' .
                          'ArminVieweg\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_',
            ],
        ],
        'configuration' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.configuration',
            'config' => [
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => [
                    'mode' => 'xml',
                    'showTemplates' => true,
                ]
            ],
        ],
        'map_to' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.mapTo',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'ArminVieweg\Dce\UserFunction\ItemsProcFunc->getAvailableTtContentColumnsForTcaMapping',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1
            ],
            'displayCond' => 'FIELD:parent_field:=:0'
        ],
        'new_tca_field_name' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.newTcaFieldName',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,required,lower'
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:parent_field:=:0',
                    'FIELD:map_to:=:*newcol'
                ]
            ],
        ],
        'new_tca_field_type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.newTcaFieldType',
            'config' => [
                'type' => 'input',
                'default' => 'auto',
                'eval' => 'trim,required'
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:parent_field:=:0',
                    'FIELD:map_to:=:*newcol'
                ]
            ],
        ],
        'section_fields' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.section_fields',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'foreign_sortby' => 'sorting',
                'foreign_field' => 'parent_field',
                'foreign_record_defaults' => [
                    'parent_field' => -1
                ],
                'minitems' => 0,
                'maxitems' => 999,
                'appearance' => [
                    'collapseAll' => 0,
                    'expandSingle' => 0,
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
        'section_fields_tag' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.section_fields_tag',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,required'
            ],
        ],
        'parent_dce' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.parent_dce',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'parent_field' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.parent_field',
            'config' => [
                'type' => 'passthrough',
                'default' => 0
            ],
        ],
    ],
];

return $dceFieldTca;
