<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 * Abstract class for DCE form validators
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class tx_dce_abstract_formeval {

	/**
	 * JavaScript validation
	 * @return string javascript function code for js validation
	 */
	public function returnFieldJS() {
		return 'return value;';
	}

	/**
	 * PHP Validation
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function evaluateFieldValue($value) {
		return $value;
	}

	/**
	 * Adds a flash message
	 *
	 * @param string $message
	 * @param string $title optional message title
	 * @param integer $severity optional severity code. One of the t3lib_FlashMessage constants
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	protected function addFlashMessage($message, $title = '', $severity = t3lib_FlashMessage::OK) {
		if (!is_string($message)) {
			throw new InvalidArgumentException('The flash message must be string, ' . gettype($message) . ' given.', 1243258395);
		}

		$flashMessage = t3lib_div::makeInstance(
			't3lib_FlashMessage',
			$message,
			$title,
			$severity,
			TRUE
		);
		t3lib_FlashMessageQueue::addMessage($flashMessage);
	}

	/**
	 * Returns the translation of current language, stored in locallang_db.xml.
	 *
	 * @param string $key key in locallang_db.xml to translate
	 * @param array $arguments optional arguments
	 * @return string Translated text
	 */
	protected function translate($key, array $arguments = array()) {
		return Tx_Extbase_Utility_Localization::translate('LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:' . $key, 'Dce', $arguments);
	}
}
?>

