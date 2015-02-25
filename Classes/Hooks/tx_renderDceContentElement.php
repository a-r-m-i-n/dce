<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Render DCE Content Element Hook
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		if (strpos($row['CType'], 'dce_dceuid') !== FALSE) {
			$drawItem = FALSE;
			$itemContent .= $parentObject->linkEditContent($row['bodytext'], $row) . '<br />';
		}
	}
}