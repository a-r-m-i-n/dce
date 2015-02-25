<?php
namespace DceTeam\Dce\Utility;
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
 * Utility for TypoScript
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */


class TypoScript {
	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager = NULL;

	/**
	 * Injects the configurationManager
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 *
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Initialize this settings utility
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	/**
	 * Converts given TypoScript string to array
	 *
	 * @param string $typoScriptString Typoscript text piece
	 * @param bool $returnPlainArray If TRUE a plain array will be returned.
	 * @return array
	 */
	public function parseTypoScriptString($typoScriptString, $returnPlainArray = FALSE) {
		/** @var \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser $typoScriptParser */
		$typoScriptParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser');
		$typoScriptParser->parse($typoScriptString);
		if ($returnPlainArray === FALSE) {
			return $typoScriptParser->setup;
		}
		return $this->convertTypoScriptArrayToPlainArray($typoScriptParser->setup);
	}

	/**
	 * Converts given array to TypoScript
	 *
	 * @param array $typoScriptArray The array to convert to string
	 * @param string $addKey Prefix given values with given key (eg. lib.whatever = {...})
	 * @param integer $tab Internal
	 * @param bool $init Internal
	 * @return string TypoScript
	 */
	public function convertArrayToTypoScript(array $typoScriptArray, $addKey = '', $tab = 0, $init = TRUE) {
		$typoScript = '';
		if ($addKey !== '') {
			$typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . $addKey . " {\n";
			if ($init === TRUE) {
				$tab++;
			}
		}
		$tab++;
		foreach($typoScriptArray as $key => $value) {
			if (!is_array($value)) {
				if (strpos($value, "\n") === FALSE) {
					$typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key = $value\n";
				} else {
					if ($key === 'configuration') {
						$valueLines = explode("\n", $value);
						$indentedValueLines = array();
						foreach ($valueLines as $valueLine) {
							$indentedValueLines[] = str_repeat("\t", $tab) . $valueLine;
						}
						$value = implode("\n", $indentedValueLines);
					}
					$typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key (\n$value\n" . str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . ")\n";
				}

			} else {
				$typoScript .= $this->convertArrayToTypoScript($value, $key, $tab, FALSE);
			}
		}
		if ($addKey !== '') {
			$tab--;
			$typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . '}';
			if ($init !== TRUE) {
				$typoScript .= "\n";
			}
		}
		return $typoScript;
	}

	/**
	 * Converts given typoScriptArray to plain array
	 *
	 * @param array $typoScriptArray
	 * @return array plain array
	 */
	public function convertTypoScriptArrayToPlainArray($typoScriptArray) {
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
			return \TYPO3\CMS\Extbase\Service\TypoScriptService::convertTypoScriptArrayToPlainArray($typoScriptArray);
		}
		return \TYPO3\CMS\Extbase\Service\TypoScriptService::convertTypoScriptArrayToPlainArray($typoScriptArray);
	}

	/**
	 * Renders a given typoscript configuration and returns the whole array with
	 * calculated values.
	 *
	 * @param array $settings the typoscript configuration array
	 * @return array the configuration array with the rendered typoscript
	 */
	public function renderConfigurationArray(array $settings) {
		$settings = $this->enhanceSettingsWithTypoScript($this->makeConfigurationArrayRenderable($settings));
		$result = array();

		foreach ($settings as $key => $value) {
			if (substr($key, -1) === '.') {
				$keyWithoutDot = substr($key, 0, -1);
				if (array_key_exists($keyWithoutDot, $settings)) {
					$result[$keyWithoutDot] = $this->contentObject->cObjGetSingle(
						$settings[$keyWithoutDot],
						$value
					);
				} else {
					$result[$keyWithoutDot] = $this->renderConfigurationArray($value);
				}
			} else {
				if (!array_key_exists($key . '.', $settings)) {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Overwrite flexform values with typoscript if flexform value is empty and typoscript value exists.
	 *
	 * @param array $settings Settings from flexform
	 * @return array enhanced settings
	 */
	protected function enhanceSettingsWithTypoScript(array $settings) {
		$extkey = 'tx_dce';
		$typoscript = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		$typoscript = $typoscript['plugin.'][$extkey . '.']['settings.'];
		foreach($settings as $key => $setting) {
			if ($setting === '' && is_array($typoscript) && array_key_exists($key, $typoscript)) {
				$settings[$key] = $typoscript[$key];
			}
		}
		return $settings;
	}

	/**
	 * Formats a given array with typoscript syntax, recursively. After the
	 * transformation it can be rendered with cObjGetSingle.
	 *
	 * Example:
	 * Before: $array['level1']['level2']['finalLevel'] = 'hello kitty'
	 * After:  $array['level1.']['level2.']['finalLevel'] = 'hello kitty'
	 *		   $array['level1'] = 'TEXT'
	 *
	 * @param array $configuration settings array to make renderable
	 * @return array the renderable settings
	 */
	protected function makeConfigurationArrayRenderable(array $configuration) {
		$dottedConfiguration = array();
		foreach ($configuration as $key => $value) {
			if (is_array($value)) {
				if (array_key_exists('_typoScriptNodeValue', $value)) {
					$dottedConfiguration[$key] = $value['_typoScriptNodeValue'];
				}
				$dottedConfiguration[$key . '.'] = $this->makeConfigurationArrayRenderable($value);
			} else {
				$dottedConfiguration[$key] = $value;
			}
		}
		return $dottedConfiguration;
	}
}