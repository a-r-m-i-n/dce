<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$boot = function($extensionKey) {
	$GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'] = PATH_typo3conf . 'temp_CACHED_dce_ext_tables.php';
	if (!file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'])) {
		/** @var $dceCache \ArminVieweg\Dce\Cache */
		$dceCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Cache');
		$dceCache->createExtTables($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);
	}
	require_once($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'ArminVieweg.' . $extensionKey,
		'tools',
		'dceModule',
		'',
		array(
			'DceModule' => 'index',
			'Dce' => 'renderPreview'
		),
		array(
			'access' => 'user,group',
			'icon' => 'EXT:' . $extensionKey . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_mod.xml',
		)
	);
};

$boot($_EXTKEY);
unset($boot);