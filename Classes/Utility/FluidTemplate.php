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
	 * @var Tx_Fluid_View_TemplateView|Tx_Fluid_View_StandaloneView the fluid template object depending on TYPO3 version
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
	 * Removes temporary created files
	 */
	public function __destruct() {
		$this->purgeTemporaryFiles();
	}

	/**
	 * Removes temporary files
	 *
	 * @return void
	 */
	protected function purgeTemporaryFiles() {
		foreach($this->temporaryFiles as $temporaryFile) {
			t3lib_div::unlink_tempfile(PATH_site . 'typo3temp/' . $temporaryFile);
		}
		$this->temporaryFiles = array();
	}

	/**
	 * Initializes the fluid template utility
	 *
	 * @return void
	 */
	protected function init() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			// If TYPO3 4.6.0 or greater
			$GLOBALS['TYPO3_DB'] =  t3lib_div::makeInstance('t3lib_DB');
			$GLOBALS['TYPO3_DB']->connectDB();

				// add extbase_object to cacheConfigurations
			$cacheConfigurations = array_merge($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'], array('extbase_object' => array()));
			$GLOBALS['typo3CacheManager']->setCacheConfigurations($cacheConfigurations);

			$this->fluidTemplate = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		} else {
			$GLOBALS['TYPO3_DB'] =  t3lib_div::makeInstance('t3lib_DB');
			$GLOBALS['TYPO3_DB']->connectDB();

			/** @var $request Tx_Extbase_MVC_Request */
			$request = t3lib_div::makeInstance('Tx_Extbase_MVC_Request');
			$request->setFormat('html');
			$request->setControllerObjectName('Tx_Dce_Controller_Dce_Controller');

			/** @var $controllerContext Tx_Extbase_MVC_Controller_ControllerContext */
			$controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
			$controllerContext->setRequest($request);

			$this->fluidTemplate = t3lib_div::makeInstance('Tx_Fluid_View_TemplateView');
			$this->fluidTemplate->setControllerContext($controllerContext);
		}

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
		$output = $this->fluidTemplate->render($actionName);
		if (get_class($this->fluidTemplate) === 'Tx_Fluid_View_TemplateView') {
			$this->purgeTemporaryFiles();
		}
		return $output;
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
	 * You can use setTemplatePathAndFilename() alternatively if you only want to specify the template path
	 *
	 * @param string $templateSource Fluid template source code
	 * @return void
	 */
	public function setSource($templateSource) {
		if (get_class($this->fluidTemplate) === 'Tx_Fluid_View_StandaloneView') {
			$this->fluidTemplate->setTemplateSource($templateSource);
		} else {
			$temporaryFile = t3lib_div::tempnam('dce_temporary_fluid_source_');
			$this->temporaryFiles[] = basename($temporaryFile);

			t3lib_div::writeFile($temporaryFile, $templateSource);
			$this->setTemplatePathAndFilename($temporaryFile);
		}
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

}