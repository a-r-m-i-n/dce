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
 *
 */
class Tx_Dce_Utility_FluidTemplate {
	/** @var string	 */
	const DEFAULT_DIRECOTRY_LAYOUTS = 'EXT:dce/Resources/Private/Layouts/';

	/** @var string	 */
	const DEFAULT_DIRECOTRY_PARTIALS = 'EXT:dce/Resources/Private/Partials/';

	/**
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $fluidTemplate = NULL;

	/**
	 * @var array with temporary files
	 */
	protected $temporaryFiles = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the fluid template utility
	 *
	 * @return void
	 */
	protected function init() {
		if (t3lib_extMgm::isLoaded('dbal')) {
			$this->assureDbalCompatibility();
		}

		// fetch the existing DB connection, or initialize it
		// needs to be done for Fluid / Cache Manager functionality
		/** @var $TYPO3_DB t3lib_DB */
		$TYPO3_DB = Tx_Dce_Utility_DatabaseUtility::getDatabaseConnection();

		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
				// If TYPO3 4.6.0 or greater
				// add extbase_object to cacheConfigurations
			$cacheConfigurations = array_merge($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'], array('extbase_object' => array()));
			$GLOBALS['typo3CacheManager']->setCacheConfigurations($cacheConfigurations);
		}
		$this->fluidTemplate = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');

		$this->fluidTemplate->setLayoutRootPath(t3lib_div::getFileAbsFileName(self::DEFAULT_DIRECOTRY_LAYOUTS));
		$this->fluidTemplate->setPartialRootPath(t3lib_div::getFileAbsFileName(self::DEFAULT_DIRECOTRY_PARTIALS));
	}

	/**
	 * Loads the template source and render the template.
	 *
	 * @param string $actionName If set, the view of the specified action will be rendered instead.
	 *        Default is the action specified in the Request object
	 * @return string Rendered Template
	 */
	public function render($actionName = NULL) {
		return $this->fluidTemplate->render($actionName);
	}

	/**
	 * Assign a value to the variable container.
	 *
	 * @param string $key The key of a view variable to set
	 * @param mixed $value The value of the view variable
	 * @return Tx_Fluid_View_AbstractTemplateView the instance of this view to allow chaining
	 */
	public function assign($key, $value) {
		return $this->fluidTemplate->assign($key, $value);
	}

	/**
	 * Sets the absolute path to a Fluid template file
	 *
	 * @param string $templatePathAndFilename Fluid template path
	 * @return void
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename) {
		$this->fluidTemplate->setTemplatePathAndFilename($templatePathAndFilename);
	}

	/**
	 * Sets the Fluid template source
	 *
	 * @param string $templateSource Fluid template source code
	 * @return void
	 */
	public function setSource($templateSource) {
		$this->fluidTemplate->setTemplateSource($templateSource);
	}

	/**
	 * Set the root path to the layouts.
	 * If set, overrides the one determined from $this->layoutRootPathPattern
	 *
	 * @param string $layoutRootPath Root path to the layouts. If set, overrides the one determined from $this->layoutRootPathPattern
	 * @return void
	 */
	public function setLayoutRootPath($layoutRootPath) {
		$this->fluidTemplate->setLayoutRootPath($layoutRootPath);
	}

	/**
	 * Sets the absolute path to the folder that contains Fluid partial files.
	 *
	 * @param string $partialRootPath Fluid partial root path
	 * @return void
	 */
	public function setPartialRootPath($partialRootPath) {
		$this->fluidTemplate->setPartialRootPath($partialRootPath);
	}

	/**
	 * Checks installation order of dbal and dce, and throws exception if dce has been loaded first. Otherwise the
	 * cache settings from dbal will be loaded to typo3CacheManager's cache configurations.
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function assureDbalCompatibility() {
		if ((t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6000000
				&& $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Database\\DatabaseConnection'] === NULL) ||
			(t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000
				&& $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_db.php'] === NULL)
		) {
			throw new Exception('When using dbal it is necessary to install the dce extension after dbal. Currently dce is loaded first.', 1358518250);
		}
		$GLOBALS['typo3CacheManager']->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
	}

}