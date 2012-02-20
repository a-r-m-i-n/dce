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
class Tx_Dce_Domain_Model_Dce extends Tx_Extbase_DomainObject_AbstractEntity {
	/** @var string */
	protected $title = '';

	/** @var Tx_Extbase_Persistence_ObjectStorage<Tx_Dce_Domain_Model_DceField> */
	protected $fields = NULL;

	/** @var string */
	protected $templateType = '';

	/** @var string */
	protected $templateContent = '';

	/** @var string */
	protected $templateFile = '';

	/** @var string */
	protected $templateLayoutRootPath = '';

	/** @var string */
	protected $templatePartialRootPath = '';


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->fields = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * @param string $templateContent
	 */
	public function setTemplateContent($templateContent) {
		$this->templateContent = $templateContent;
	}

	/**
	 * @return string
	 */
	public function getTemplateContent() {
		return $this->templateContent;
	}

	/**
	 * @param string $templateFile
	 */
	public function setTemplateFile($templateFile) {
		$this->templateFile = $templateFile;
	}

	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->templateFile;
	}

	/**
	 * @param string $templateType
	 */
	public function setTemplateType($templateType) {
		$this->templateType = $templateType;
	}

	/**
	 * @return string
	 */
	public function getTemplateType() {
		return $this->templateType;
	}

	/**
	 * @return string
	 */
	public function getTemplateLayoutRootPath() {
		return $this->templateLayoutRootPath;
	}

	/**
	 * @param string $templateLayoutRootPath
	 */
	public function setTemplateLayoutRootPath($templateLayoutRootPath) {
		$this->templateLayoutRootPath = $templateLayoutRootPath;
	}

	/**
	 * @return string
	 */
	public function getTemplatePartialRootPath() {
		return $this->templatePartialRootPath;
	}

	/**
	 * @param string $templatePartialRootPath
	 */
	public function setTemplatePartialRootPath($templatePartialRootPath) {
		$this->templatePartialRootPath = $templatePartialRootPath;
	}

	/**
	 * Gets objectStorage with fields
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Sets objectStorage with fields
	 * @param Tx_Extbase_Persistence_ObjectStorage $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}

	/**
	 * Adds a field
	 *
	 * @param Tx_Dce_Domain_Model_DceField $field The field to be added
	 * @return void
	 */
	public function addField(Tx_Dce_Domain_Model_DceField $field) {
		$this->fields->attach($field);
	}

	/**
	 * Removes a field
	 *
	 * @param Tx_Dce_Domain_Model_DceField $fieldToRemove The field to be removed
	 * @return void
	 */
	public function removeField(Tx_Dce_Domain_Model_DceField $fieldToRemove) {
		$this->fields->detach($fieldToRemove);
	}
}
?>