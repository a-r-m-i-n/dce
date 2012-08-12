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

	/** @var string */
	protected $previewTemplateType = '';

	/** @var string */
	protected $headerPreview = '';

	/** @var string */
	protected $headerPreviewTemplateFile = '';

	/** @var string */
	protected $bodytextPreview = '';

	/** @var string */
	protected $bodytextPreviewTemplateFile = '';

	/** @var boolean */
	protected $enableDetailpage = FALSE;

	/** @var string */
	protected $detailpageIdentifier = '';

	/** @var string */
	protected $detailpageTemplateType = '';

	/** @var string */
	protected $detailpageTemplate = '';

	/** @var string */
	protected $detailpageTemplateFile = '';


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

	/**
	 * @return string
	 */
	public function getPreviewTemplateType() {
		return $this->previewTemplateType;
	}

	/**
	 * @param string $previewTemplateType
	 */
	public function setPreviewTemplateType($previewTemplateType) {
		$this->previewTemplateType = $previewTemplateType;
	}

	/**
	 * @return string
	 */
	public function getHeaderPreview() {
		return $this->headerPreview;
	}

	/**
	 * @param string $headerPreview
	 */
	public function setHeaderPreview($headerPreview) {
		$this->headerPreview = $headerPreview;
	}

	/**
	 * @return string
	 */
	public function getHeaderPreviewTemplateFile() {
		return $this->headerPreviewTemplateFile;
	}

	/**
	 * @param string $headerPreviewTemplateFile
	 */
	public function setHeaderPreviewTemplateFile($headerPreviewTemplateFile) {
		$this->headerPreviewTemplateFile = $headerPreviewTemplateFile;
	}

	/**
	 * @return string
	 */
	public function getBodytextPreview() {
		return $this->bodytextPreview;
	}

	/**
	 * @param string $bodytextPreview
	 */
	public function setBodytextPreview($bodytextPreview) {
		$this->bodytextPreview = $bodytextPreview;
	}

	/**
	 * @return string
	 */
	public function getBodytextPreviewTemplateFile() {
		return $this->bodytextPreviewTemplateFile;
	}

	/**
	 * @param string $bodytextPreviewTemplateFile
	 */
	public function setBodytextPreviewTemplateFile($bodytextPreviewTemplateFile) {
		$this->bodytextPreviewTemplateFile = $bodytextPreviewTemplateFile;
	}

	/**
	 * @return boolean
	 */
	public function getEnableDetailpage() {
		return $this->enableDetailpage;
	}

	/**
	 * @param boolean $enableDetailpage
	 */
	public function setEnableDetailpage($enableDetailpage) {
		$this->enableDetailpage = $enableDetailpage;
	}

	/**
	 * @return string
	 */
	public function getDetailpageIdentifier() {
		return $this->detailpageIdentifier;
	}

	/**
	 * @param string $detailpageIdentifier
	 */
	public function setDetailpageIdentifier($detailpageIdentifier) {
		$this->detailpageIdentifier = $detailpageIdentifier;
	}

	/**
	 * @return string
	 */
	public function getDetailpageTemplateType() {
		return $this->detailpageTemplateType;
	}

	/**
	 * @param string $detailpageTemplateType
	 */
	public function setDetailpageTemplateType($detailpageTemplateType) {
		$this->detailpageTemplateType = $detailpageTemplateType;
	}

	/**
	 * @return string
	 */
	public function getDetailpageTemplate() {
		return $this->detailpageTemplate;
	}

	/**
	 * @param string $detailpageTemplate
	 */
	public function setDetailpageTemplate($detailpageTemplate) {
		$this->detailpageTemplate = $detailpageTemplate;
	}

	/**
	 * @return string
	 */
	public function getDetailpageTemplateFile() {
		return $this->detailpageTemplateFile;
	}

	/**
	 * @param string $detailpageTemplateFile
	 */
	public function setDetailpageTemplateFile($detailpageTemplateFile) {
		$this->detailpageTemplateFile = $detailpageTemplateFile;
	}

}
?>