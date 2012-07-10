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
 * TCA custom validator which checks lowerCamelCase.
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class tx_dce_formevals_lowerCamelCase extends tx_dce_abstract_formeval {

	/**
	 * PHP Validation to check lowerCamelCase
	 *
	 * @param string $value
	 * @return mixed|string Updated string, which fits the requirements
	 */
	public function evaluateFieldValue($value) {
		$originalValue = $value;
		$value = t3lib_div::underscoredToLowerCamelCase(str_replace('-', '_', $value));

		if ($originalValue !== $value && !empty($value)) {
			$this->addFlashMessage(
				$this->translate('tx_dce_formeval_lowerCamelCase', array($originalValue, $value)),
				$this->translate('tx_dce_formeval_headline'),
				t3lib_FlashMessage::NOTICE
			);
		}
		return $value;
	}
}
?>