<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Render DCE Content Element Hook
 *
 * @package ArminVieweg\Dce
 */
class tx_renderDceContentElement implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface  {
	/**
	 * Disable rendering restrictions for dce content elements
	 *
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject
	 * @param $drawItem
	 * @param $headerContent
	 * @param $itemContent
	 * @param array $row#
	 * @return void
	 */
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent,
							   array &$row) {
		if (strpos($row['CType'], 'dce_dceuid') !== FALSE) {
			$drawItem = FALSE;
			$itemContent .= $parentObject->linkEditContent($row['bodytext'], $row) . '<br />';
		}
	}
}