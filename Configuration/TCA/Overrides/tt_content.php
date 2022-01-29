<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$newTtContentColumns = [
    'tx_dce_dce' => [
        'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tt_content.tx_dce_dce',
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
    'tx_dce_index' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'tx_dce_slug' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'tx_dce_new_container' => [
        'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tt_content.tx_dce_new_container',
        'config' => [
            'type' => 'check',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $newTtContentColumns);

if (!isset($GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'])) {
    $GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'] =
        'T3\Dce\UserFunction\CustomLabels\TtContentLabel->getLabel';
}

// TCA generation
$generator = new \T3\Dce\Components\ContentElementGenerator\Generator();
$generator->makeTca();
