<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
	/** Identifier for default DCE templates */
	const TEMPLATE_FIELD_DEFAULT = 0;
	/** Identifier for header preview templates */
	const TEMPLATE_FIELD_HEADERPREVIEW = 1;
	/** Identifier for bodytext preview templates */
	const TEMPLATE_FIELD_BODYTEXTPREVIEW = 2;
	/** Identifier for detail page templates */
	const TEMPLATE_FIELD_DETAILPAGE = 3;

	/** @var array database field names of columns for different types of templates */
	protected $templateFields = array(
		self::TEMPLATE_FIELD_DEFAULT => array('type' => 'template_type', 'inline' => 'template_content', 'file' => 'template_file'),
		self::TEMPLATE_FIELD_HEADERPREVIEW => array('type' => 'preview_template_type', 'inline' => 'header_preview', 'file' => 'header_preview_template_file'),
		self::TEMPLATE_FIELD_BODYTEXTPREVIEW => array('type' => 'preview_template_type', 'inline' => 'bodytext_preview', 'file' => 'bodytext_preview_template_file'),
		self::TEMPLATE_FIELD_DETAILPAGE => array('type' => 'detailpage_template_type', 'inline' => 'detailpage_template', 'file' => 'detailpage_template_file'),
	);

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

	/** @var array */
	protected $_contentObject = array();


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
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
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

	/**
	 * Checks attached fields for given variable and returns the single field if found. If not found, returns NULL.
	 *
	 * @param string $variable
	 * @return null|Tx_Dce_Domain_Model_DceField
	 */
	public function getFieldByVariable($variable) {
		/** @var $field Tx_Dce_Domain_Model_DceField */
		foreach($this->getFields() as $field) {
			if ($field->getVariable() === $variable) {
				return $field;
			}
		}
		return NULL;
	}

	/**
	 * @return array
	 */
	public function getContentObject() {
		return $this->_contentObject;
	}

	/**
	 * @param array $contentObject
	 */
	public function setContentObject($contentObject) {
		$this->_contentObject = $contentObject;
	}

	/**
	 * Renders the default DCE output
	 * @return string rendered output
	 */
	public function render() {
		return $this->renderFluidTemplate();
	}

	/**
	 * Renders the DCE detail page output
	 * @return string rendered output
	 */
	public function renderDetailpage() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_DETAILPAGE);
	}

	/**
	 * Renders the HeaderPreview output
	 * @return string rendered output
	 */
	public function renderHeaderPreview() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_HEADERPREVIEW);
	}

	/**
	 * Renders the BodytextPreview output
	 * @return string rendered output
	 */
	public function renderBodytextPreview() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_BODYTEXTPREVIEW);
	}

	/**
	 * Creates a fluid template
	 *
	 * @param integer $templateType
	 * @return Tx_Fluid_View_StandaloneView
	 */
	protected function renderFluidTemplate($templateType = self::TEMPLATE_FIELD_DEFAULT) {
		$templateFields = $this->templateFields[$templateType];
		$typeGetter = 'get' . ucfirst(t3lib_div::underscoredToLowerCamelCase($templateFields['type']));

		/** @var $fluidTemplate Tx_Fluid_View_StandaloneView */
		$fluidTemplate = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		if ($this->$typeGetter() === 'inline') {
			$inlineTemplateGetter = 'get' . ucfirst(t3lib_div::underscoredToLowerCamelCase($templateFields['inline']));
			$fluidTemplate->setTemplateSource($this->$inlineTemplateGetter());
		} else {
			$fileTemplateGetter = 'get' . ucfirst(t3lib_div::underscoredToLowerCamelCase($templateFields['file']));
			$filePath = PATH_site . $this->$fileTemplateGetter();
			if (!file_exists($filePath)) {
				$fluidTemplate->setTemplateSource('');
			} else {
				$fluidTemplate->setTemplatePathAndFilename($filePath);
			}
		}
		$fluidTemplate->setLayoutRootPath(t3lib_div::getFileAbsFileName($this->getTemplateLayoutRootPath()));
		$fluidTemplate->setPartialRootPath(t3lib_div::getFileAbsFileName($this->getTemplatePartialRootPath()));

		$fluidTemplate->assign('dce', $this);
		$fluidTemplate->assign('contentObject', $this->getContentObject());

		if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
			$fluidTemplate->assign('TSFE', $GLOBALS['TSFE']);
			$fluidTemplate->assign('page', $GLOBALS['TSFE']->page);
			$fluidTemplate->assign('tsSetup', Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup));
		}

		$fields = $this->getFieldsAsArray();
		$fluidTemplate->assign('field', $fields);
		$fluidTemplate->assign('fields', $fields);

		return $fluidTemplate->render();
	}

	/**
	 * Returns fields of DCE. Key is variable, value is the value of the field.
	 *
	 * @return array
	 */
	protected function getFieldsAsArray() {
		$fields = array();
		/** @var $field Tx_Dce_Domain_Model_DceField */
		foreach($this->getFields() as $field) {
			if ($field->isTab()) {
				continue;
			}
			if ($field->getSectionFields()) {
				/**	@var $sectionField Tx_Dce_Domain_Model_DceField */
				foreach($field->getSectionFields() as $sectionField) {
					$sectionFieldValues = $sectionField->getValue();
					if (is_array($sectionFieldValues)) {
						foreach ($sectionFieldValues as $i => $value) {
							$fields[$field->getVariable()][$i][$sectionField->getVariable()] = $value;
						}
					}
				}
			}
			else {
				$fields[$field->getVariable()] = $field->getValue();
			}
		}
		return $fields;
	}

	/**
	 * Magic PHP method.
	 * Checks if called and not existing method begins with "get". If yes, extract the part behind the get.
	 * If a method in $this exists which matches this part, it will be called. Otherwise it will be searched in
	 * $this->fields for the part. If the field exist its value will returned.
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, array $arguments) {
		if (substr($name, 0, 3) === 'get' && strlen($name) > 3) {
			$variable = lcfirst(substr($name, 3));
			if (method_exists($this, $variable)) {
				return $this->$variable();
			}

			$field = $this->getFieldByVariable($variable);
			if (get_class($field) === 'Tx_Dce_Domain_Model_DceField') {
				return $field->getValue();
			}
		}
		return;
	}

}
?>