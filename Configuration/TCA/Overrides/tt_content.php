<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$newTtContentColumns = array(
    'tx_dce_dce' => array(
        'label' => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tt_content.tx_dce_dce',
        'config' => array(
            'type' => 'select',
            'foreign_table' => 'tx_dce_domain_model_dce',
            'items' => array(
                array('', ''),
            ),
            'minitems' => 0,
            'maxitems' => 1,
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $newTtContentColumns);
