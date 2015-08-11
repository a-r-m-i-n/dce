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

return array(
    'ctrl' => array(
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
        'versioningWS' => 2,
        'versioning_followPages' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'requestUpdate' => 'type',
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicons' => array(
            '0' => $extensionPath . 'Resources/Public/Icons/tx_dce_domain_model_dcefield_element.png',
            '1' => $extensionPath . 'Resources/Public/Icons/tx_dce_domain_model_dcefield_tab.png',
            '2' => $extensionPath . 'Resources/Public/Icons/tx_dce_domain_model_dcefield_section.png',
        ),
    ),
    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden',
    ),
    'types' => array(
        '0' => array(
            'showitem' => 'type,title,variable,configuration;;;fixed-font:enable-tab,
			--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,hidden;;1'
        ),
        '1' => array(
            'showitem' => 'type,title,
			--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,hidden;;1'
        ),
        '2' => array(
            'showitem' => 'type,title,section_fields_tag,variable,section_fields,
			--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,hidden;;1'
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
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'foreign_table_where' => 'AND tx_dce_domain_model_dcefield.pid=###CURRENT_PID### ' .
                    'AND tx_dce_domain_model_dcefield.sys_language_uid IN (-1,0)',
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
        'starttime' => array(
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
        'endtime' => array(
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
        'type' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.type',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array($ll . 'tx_dce_domain_model_dcefield.type.element', 0),
                    array($ll . 'tx_dce_domain_model_dcefield.type.tab', 1),
                    array($ll . 'tx_dce_domain_model_dcefield.type.section', 2),
                ),
            ),
        ),
        'title' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.title',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ),
        ),
        'variable' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.variable',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required,is_in,' .
                          'ArminVieweg\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator,' .
                          'ArminVieweg\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator',
                'is_in' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_',
            ),
        ),
        'configuration' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.configuration',
            'config' => array(
                'type' => 'user',
                'size' => '30',
                'userFunc' => 'EXT:dce/Classes/UserFunction/UserFields/CodemirrorField.php:' .
                    'ArminVieweg\Dce\UserFunction\UserFields\CodemirrorField->getCodemirrorField',
                'parameters' => array(
                    'mode' => 'xml',
                    'showTemplates' => true,
                )
            ),
        ),
        'section_fields' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.section_fields',
            'config' => array(
                'type' => 'inline',
                'foreign_table' => 'tx_dce_domain_model_dcefield',
                'MM' => 'tx_dce_dcefield_sectionfields_mm',
                'minitems' => 0,
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
        'section_fields_tag' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_dce_domain_model_dcefield.section_fields_tag',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ),
        ),
    ),
);
