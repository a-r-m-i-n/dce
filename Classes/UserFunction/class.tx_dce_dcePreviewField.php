<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class tx_dce_dcePreviewField {
	/** PID where all DCE previews are temporary stored with */
	const DCE_PREVIEW_PID = -5;

	/**
	 * @param array $parameter unused
	 * @param t3lib_TCEforms $fObj
	 * @return string
	 */
	function getPreview($parameter, t3lib_TCEforms $fObj) {
		/** @var $fluidTemplate Tx_Dce_Utility_FluidTemplate */
		$fluidTemplate = t3lib_div::makeInstance('Tx_Dce_Utility_FluidTemplate');

		$fluidTemplate->setLayoutRootPath(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Layouts/'));
		$fluidTemplate->setPartialRootPath(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Partials/'));
		$fluidTemplate->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Templates/DceUserFields/DcePreview.html'));

		$dceUid = $this->getDceUid($fObj);
		$fluidTemplate->assign('dceUid', $dceUid);

		$previewItemUid = $this->createDceInstancePreviewItem($dceUid);
		$fluidTemplate->assign('previewItemUid', $previewItemUid);

		return $fluidTemplate->render();
	}

	/**
	 * Creates a new content item as instance of DCE with given uid, for preview purposes.
	 *
	 * @param integer $dceUid
	 * @return integer uid of created item
	 */
	protected function createDceInstancePreviewItem($dceUid) {
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_content', array(
			'pid' => self::DCE_PREVIEW_PID,
			'tstamp' => time(),
			'crdate' => time(),
			'list_type' => '',
			'CType' => 'dce_dceuid' . $dceUid,
		));
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * @param t3lib_TCEforms $fObj
	 * @return integer
	 */
	protected function getDceUid(t3lib_TCEforms $fObj) {
		$dceRecord = current($fObj->cachedTSconfig);
		return intval($dceRecord['_THIS_UID']);
	}
}