<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */

use T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator;
use T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator;

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
                           --palette--;;tca_options',
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
            'showitem' => '--palette--;;general_header'
        ],
        '2' => [
            'showitem' => '--palette--;;general_header,section_fields_tag,section_fields'
        ],
    ],
    'palettes' => [
        'general_header' => ['showitem' => 'type,title,variable,hidden'],
        'tca_options' => ['showitem' => 'map_to,new_tca_field_name,new_tca_field_type']
    ],
    'columns' => [
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
                    [
                        'label' => $ll . 'tx_dce_domain_model_dcefield.type.element',
                        'value' => 0,
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dcefield.type.tab',
                        'value' => 1,
                    ],
                    [
                        'label' => $ll . 'tx_dce_domain_model_dcefield.type.section',
                        'value' => 2,
                    ],
                ],
            ],
            'displayCond' => 'FIELD:parent_field:REQ:false'
        ],
        'title' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.title',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'required' => true,
                'eval' => 'trim'
            ],
        ],
        'variable' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.variable',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'required' => true,
                'eval' => 'trim,is_in,' . NoLeadingNumberValidator::class . ',' . LowerCamelCaseValidator::class,
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
            'displayCond' => 'FIELD:parent_field:REQ:false'
        ],
        'new_tca_field_name' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.newTcaFieldName',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim,lower'
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:parent_field:REQ:false',
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
                'required' => true,
                'eval' => 'trim'
            ],
            'displayCond' => [
                'AND' => [
                    'FIELD:parent_field:REQ:false',
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
                'required' => true,
                'eval' => 'trim'
            ],
        ],
        'parent_dce' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.parent_dce',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_dce_domain_model_dce',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0
            ],
        ],
        'parent_field' => [
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.parent_field',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0
            ],
        ],
    ],
];

return $dceFieldTca;
