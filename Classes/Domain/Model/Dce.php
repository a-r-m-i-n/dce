<?php
namespace DceTeam\Dce\Domain\Model;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Model for DCEs. This model contains all necessary informations
 * to render the content element in frontend.
 *
 * @package DceTeam\Dce
 */
class Dce extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
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

	/** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DceTeam\Dce\Domain\Model\DceField> */
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

	/** @var bool */
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
	 * Constructor
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->fields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTemplateContent() {
		return $this->templateContent;
	}

	/**
	 * @param string $templateContent
	 * @return void
	 */
	public function setTemplateContent($templateContent) {
		$this->templateContent = $templateContent;
	}

	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->templateFile;
	}

	/**
	 * @param string $templateFile
	 * @return void
	 */
	public function setTemplateFile($templateFile) {
		$this->templateFile = $templateFile;
	}

	/**
	 * @return string
	 */
	public function getTemplateType() {
		return $this->templateType;
	}

	/**
	 * @param string $templateType
	 * @return void
	 */
	public function setTemplateType($templateType) {
		$this->templateType = $templateType;
	}

	/**
	 * @return string
	 */
	public function getTemplateLayoutRootPath() {
		return $this->templateLayoutRootPath;
	}

	/**
	 * @param string $templateLayoutRootPath
	 * @return void
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
	 * @return void
	 */
	public function setTemplatePartialRootPath($templatePartialRootPath) {
		$this->templatePartialRootPath = $templatePartialRootPath;
	}

	/**
	 * Gets objectStorage with fields
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DceField>
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Sets objectStorage with fields
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DceField> $fields
	 * @return void
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}

	/**
	 * Adds a field
	 *
	 * @param \DceTeam\Dce\Domain\Model\DceField $field The field to be added
	 * @return void
	 */
	public function addField(\DceTeam\Dce\Domain\Model\DceField $field) {
		$this->fields->attach($field);
	}

	/**
	 * Removes a field
	 *
	 * @param \DceTeam\Dce\Domain\Model\DceField $fieldToRemove The field to be removed
	 * @return void
	 */
	public function removeField(\DceTeam\Dce\Domain\Model\DceField $fieldToRemove) {
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
	 * @return void
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
	 * @return void
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
	 * @return void
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
	 * @return void
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
	 * @return void
	 */
	public function setBodytextPreviewTemplateFile($bodytextPreviewTemplateFile) {
		$this->bodytextPreviewTemplateFile = $bodytextPreviewTemplateFile;
	}

	/**
	 * @return bool
	 */
	public function getEnableDetailpage() {
		return $this->enableDetailpage;
	}

	/**
	 * @param bool $enableDetailpage
	 * @return void
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
	 * @return void
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
	 * @return void
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
	 * @return void
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
	 * @return void
	 */
	public function setDetailpageTemplateFile($detailpageTemplateFile) {
		$this->detailpageTemplateFile = $detailpageTemplateFile;
	}

	/**
	 * Checks attached fields for given variable and returns the single field if found.
	 * If not found, returns NULL.
	 *
	 * @param string $variable
	 * @return NULL|DceField
	 */
	public function getFieldByVariable($variable) {
		/** @var $field DceField */
		foreach ($this->getFields() as $field) {
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
	 * @return void
	 */
	public function setContentObject($contentObject) {
		$this->_contentObject = $contentObject;
	}

	/**
	 * Renders the default DCE output
	 *
	 * @return string rendered output
	 */
	public function render() {
		return $this->renderFluidTemplate();
	}

	/**
	 * Renders the DCE detail page output
	 *
	 * @return string rendered output
	 */
	public function renderDetailpage() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_DETAILPAGE);
	}

	/**
	 * Renders the HeaderPreview output
	 *
	 * @return string rendered output
	 */
	public function renderHeaderPreview() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_HEADERPREVIEW);
	}

	/**
	 * Renders the BodytextPreview output
	 *
	 * @return string rendered output
	 */
	public function renderBodytextPreview() {
		return $this->renderFluidTemplate(self::TEMPLATE_FIELD_BODYTEXTPREVIEW);
	}

	/**
	 * Creates and renders fluid template
	 *
	 * @param int $templateType
	 * @return string Rendered and trimmed template
	 */
	protected function renderFluidTemplate($templateType = self::TEMPLATE_FIELD_DEFAULT) {
		$templateFields = $this->templateFields[$templateType];
		$typeGetter = 'get' . ucfirst(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($templateFields['type']));

		/** @var $fluidTemplate \TYPO3\CMS\Fluid\View\StandaloneView */
		$fluidTemplate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Fluid\View\StandaloneView');
		if ($this->$typeGetter() === 'inline') {
			$inlineTemplateGetter = 'get' . ucfirst(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($templateFields['inline']));
			$fluidTemplate->setTemplateSource($this->$inlineTemplateGetter() . ' ');
		} else {
			$fileTemplateGetter = 'get' . ucfirst(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($templateFields['file']));
			$filePath = \DceTeam\Dce\Utility\File::getFilePath($this->$fileTemplateGetter());
			if (!file_exists($filePath)) {
				$fluidTemplate->setTemplateSource('');
			} else {
				$templateContent = file_get_contents($filePath);
				$fluidTemplate->setTemplateSource($templateContent . ' ');
			}
		}

		$fluidTemplate->setLayoutRootPath(\DceTeam\Dce\Utility\File::getFilePath($this->getTemplateLayoutRootPath()));
		$fluidTemplate->setPartialRootPath( \DceTeam\Dce\Utility\File::getFilePath($this->getTemplatePartialRootPath()));

		$fluidTemplate->assign('dce', $this);
		$fluidTemplate->assign('contentObject', $this->getContentObject());

		if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
			$fluidTemplate->assign('TSFE', $GLOBALS['TSFE']);
			$fluidTemplate->assign('page', $GLOBALS['TSFE']->page);

			$typoScriptService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Service\TypoScriptService');
			$fluidTemplate->assign('tsSetup', $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup));
		}

		$fields = $this->getFieldsAsArray();
		$fluidTemplate->assign('field', $fields);
		$fluidTemplate->assign('fields', $fields);

		return trim($fluidTemplate->render());
	}

	/**
	 * Returns fields of DCE. Key is variable, value is the value of the field.
	 *
	 * @return array Fields of DCE
	 */
	protected function getFieldsAsArray() {
		$fields = array();
		/** @var $field \DceTeam\Dce\Domain\Model\DceField */
		foreach ($this->getFields() as $field) {
			if ($field->isTab()) {
				continue;
			}
			if ($field->hasSectionFields()) {
				/**	@var $sectionField \DceTeam\Dce\Domain\Model\DceField  */
				foreach ($field->getSectionFields() as $sectionField) {
					$sectionFieldValues = $sectionField->getValue();
					if (is_array($sectionFieldValues)) {
						foreach ($sectionFieldValues as $i => $value) {
							$fields[$field->getVariable()][$i][$sectionField->getVariable()] = $value;
						}
					}
				}
			} else {
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
	 * @return mixed
	 */
	public function __call($name, array $arguments) {
		if (substr($name, 0, 3) === 'get' && strlen($name) > 3) {
			$variable = lcfirst(substr($name, 3));
			if (method_exists($this, $variable)) {
				return $this->$variable();
			}

			$field = $this->getFieldByVariable($variable);
			if (get_class($field) === '\DceTeam\Dce\Domain\Model\DceField') {
				return $field->getValue();
			}
		}
		return NULL;
	}
}