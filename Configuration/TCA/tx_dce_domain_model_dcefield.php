<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:';
$extensionPath = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce')
);

$dceFieldTca = [
    'ctrl' => [
        'title' => $ll . 'tx_dce_domain_model_dcefield',
        'label' => 'title',
        'label_userFunc' => 'T3\Dce\UserFunction\CustomLabels\DceFieldLabel->getLabel',
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
        ],
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            '0' => 'ext-dce-dcefield-type-element',
            '1' => 'ext-dce-dcefield-type-tab',
            '2' => 'ext-dce-dcefield-type-section'
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;;general_header,configuration,
                           --palette--;;tca_options,parent_dce,parent_field',
            'columnsOverrides' => [
                'configuration' => [
                    'config' => [
                        'fixedFont' => true,
                        'enableTabulator' => true
                    ]
                ],
            ],
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
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
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
        'type' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.type',
            'onChange' => 'reload',
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
                'eval' => 'trim,required,is_in,
                           T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator,
                           T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_',
            ],
        ],
        'configuration' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.configuration',
            'config' => [
                'type' => 'text',
                'renderType' => 'dceCodeMirrorField',
                'size' => '30',
                'parameters' => [
                    'mode' => 'xml',
                    'showTemplates' => true,
                ],
                'default' => '<config>
    <type>input</type>
</config>'
            ],
        ],
        'map_to' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.mapTo',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => T3\Dce\UserFunction\ItemsProcFunc::class .
                                    '->getAvailableTtContentColumnsForTcaMapping',
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
                'overrideChildTca' => [
                    'columns' => [
                        'parent_field' => [
                            'config' => [
                                'default' => -1
                            ]
                        ]
                    ]
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

if (!\T3\Dce\Compatibility::isTypo3Version('10.0.0')) {
    $dceFieldTca['interface'] = [
        'showRecordFieldList' => 'hidden,title,type,variable',
    ];
}

return $dceFieldTca;
