<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// Save hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/tx_saveDce.php:tx_saveDce';

if ($extConfiguration['DISABLEDPREVIEWAUTOUPDATE'] == 1) {
	$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['AJAX']['Dce::updateContentElement']
		= 'EXT:dce/Classes/Hooks/tx_update_contentelement.php:tx_update_contentelement->updateContentElement';
}

// User conditions
include_once(t3lib_extMgm::extPath('dce') . 'Classes/UserConditions/user_dceOnCurrentPage.php');

// Rendering hook of content elements in backend
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/tx_renderDceContentElement.php:tx_renderDceContentElement';

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php']
	= t3lib_extMgm::extPath($_EXTKEY).'Classes/Hooks/class.ux_t3lib_tcemain.php';

if (TYPO3_MODE === 'BE') {
	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/UserFunction/class.tx_dce_codemirrorField.php');
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/UserFunction/class.tx_dce_dceFieldCustomLabel.php');
}

// Special tce validators (eval)
include_once(t3lib_extMgm::extPath('dce') . 'Classes/UserFunction/class.tx_dce_abstract_formeval.php');
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_dce_formevals_lowerCamelCase'] = 'EXT:dce/Classes/UserFunction/class.tx_dce_formevals_lowerCamelCase.php';
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_dce_formevals_noLeadingNumber'] = 'EXT:dce/Classes/UserFunction/class.tx_dce_formevals_noLeadingNumber.php';

$pathDceLocalconf = PATH_typo3conf . 'temp_CACHED_dce_ext_localconf.php';
if (!file_exists($pathDceLocalconf)) {
	/** @var $dceCache Tx_Dce_Cache */
	$dceCache = t3lib_div::makeInstance('Tx_Dce_Cache');
	$dceCache->createLocalconf($pathDceLocalconf);
}
require_once($pathDceLocalconf);
?>