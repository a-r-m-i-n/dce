<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$boot = function($extensionKey) {
	$GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'] = PATH_typo3conf . 'temp_CACHED_dce_ext_tables.php';
	if (!file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'])) {
		/** @var $dceCache Tx_Dce_Cache */
		$dceCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Dce_Cache');
		$dceCache->createExtTables($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);
	}
	require_once($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		$extensionKey,
		'tools',
		'dceModule',
		'',
		array(
			'DceModule' => 'index,dcePreviewReturnPage',
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