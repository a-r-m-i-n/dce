<?php
namespace ArminVieweg\Dce\Domain\Model;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Model for DCEs. This model contains all necessary informations
 * to render the content element in frontend.
 *
 * @package ArminVieweg\Dce
 */
class Dce extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /** Identifier for default DCE templates */
    const TEMPLATE_FIELD_DEFAULT = 0;
    /** Identifier for header preview templates */
    const TEMPLATE_FIELD_HEADERPREVIEW = 1;
    /** Identifier for bodytext preview templates */
    const TEMPLATE_FIELD_BODYTEXTPREVIEW = 2;
    /** Identifier for detail page templates */
    const TEMPLATE_FIELD_DETAILPAGE = 3;
    /** Identifier for dce container templates */
    const TEMPLATE_FIELD_CONTAINER = 4;

    /** Type for databased stored DCEs */
    const TYPE_DB = 0;
    /** Type for filebased DCEs */
    const TYPE_FILE = 1;

    /**
     * @var array Cache for fluid instances
     */
    static protected $fluidTemplateCache = array();

    /**
     * @var array Cache for DceFields
     */
    static protected $fieldsCache = array();

    /**
     * @var array Cache for content element rows
     */
    static protected $contentElementRowsCache = array();

    /** @var array database field names of columns for different types of templates */
    protected $templateFields = array(
        self::TEMPLATE_FIELD_DEFAULT => array(
            'type' => 'template_type',
            'inline' => 'template_content',
            'file' => 'template_file'
        ),
        self::TEMPLATE_FIELD_HEADERPREVIEW => array(
            'type' => 'preview_template_type',
            'inline' => 'header_preview',
            'file' => 'header_preview_template_file'
        ),
        self::TEMPLATE_FIELD_BODYTEXTPREVIEW => array(
            'type' => 'preview_template_type',
            'inline' => 'bodytext_preview',
            'file' => 'bodytext_preview_template_file'
        ),
        self::TEMPLATE_FIELD_DETAILPAGE => array(
            'type' => 'detailpage_template_type',
            'inline' => 'detailpage_template',
            'file' => 'detailpage_template_file'
        ),
        self::TEMPLATE_FIELD_CONTAINER => array(
            'type' => 'container_template_type',
            'inline' => 'container_template',
            'file' => 'container_template_file'
        )
    );

    /**
     * @var bool
     */
    protected $hidden = false;

    /** @var int */
    protected $type = self::TYPE_DB;

    /** @var string */
    protected $title = '';

    /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\ArminVieweg\Dce\Domain\Model\DceField> */
    protected $fields = null;

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

    /** @var bool */
    protected $useSimpleBackendView = false;

    /** @var string */
    protected $backendViewHeader = '';

    /** @var string */
    protected $backendViewBodytext = '';

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
    protected $enableDetailpage = false;

    /** @var string */
    protected $detailpageIdentifier = '';

    /** @var string */
    protected $detailpageTemplateType = '';

    /** @var string */
    protected $detailpageTemplate = '';

    /** @var string */
    protected $detailpageTemplateFile = '';

    /** @var bool */
    protected $enableContainer = false;

    /** @var int */
    protected $containerItemLimit = 0;

    /** @var string */
    protected $containerTemplateType = '';

    /** @var string */
    protected $containerTemplate = '';

    /** @var string */
    protected $containerTemplateFile = '';

    /** @var bool  */
    protected $wizardEnable = true;

    /** @var string */
    protected $wizardCategory = '';

    /** @var string */
    protected $wizardDescription = '';

    /** @var string */
    protected $wizardIcon = '';

    /** @var string */
    protected $wizardCustomIcon = '';

    /** @var array */
    protected $_contentObject = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->fields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param boolean $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }

    /**
     * @param string $templateContent
     * @return void
     */
    public function setTemplateContent($templateContent)
    {
        $this->templateContent = $templateContent;
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     * @return void
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    /**
     * @return string
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * @param string $templateType
     * @return void
     */
    public function setTemplateType($templateType)
    {
        $this->templateType = $templateType;
    }

    /**
     * @return string
     */
    public function getTemplateLayoutRootPath()
    {
        return $this->templateLayoutRootPath;
    }

    /**
     * @param string $templateLayoutRootPath
     * @return void
     */
    public function setTemplateLayoutRootPath($templateLayoutRootPath)
    {
        $this->templateLayoutRootPath = $templateLayoutRootPath;
    }

    /**
     * @return string
     */
    public function getTemplatePartialRootPath()
    {
        return $this->templatePartialRootPath;
    }

    /**
     * @param string $templatePartialRootPath
     * @return void
     */
    public function setTemplatePartialRootPath($templatePartialRootPath)
    {
        $this->templatePartialRootPath = $templatePartialRootPath;
    }

    /**
     * Gets objectStorage with fields
     *
     * @return DceField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Sets objectStorage with fields
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DceField> $fields
     * @return void
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Adds a field
     *
     * @param \ArminVieweg\Dce\Domain\Model\DceField $field The field to be added
     * @return void
     */
    public function addField(\ArminVieweg\Dce\Domain\Model\DceField $field)
    {
        $this->fields->attach($field);
    }

    /**
     * Removes a field
     *
     * @param \ArminVieweg\Dce\Domain\Model\DceField $fieldToRemove The field to be removed
     * @return void
     */
    public function removeField(\ArminVieweg\Dce\Domain\Model\DceField $fieldToRemove)
    {
        $this->fields->detach($fieldToRemove);
    }

    /**
     * Get UseSimpleBackendView
     *
     * @return boolean
     */
    public function getUseSimpleBackendView()
    {
        return $this->useSimpleBackendView;
    }

    /**
     * Get UseSimpleBackendView
     *
     * @return boolean
     */
    public function isUseSimpleBackendView()
    {
        return $this->useSimpleBackendView;
    }

    /**
     * Set UseSimpleBackendView
     *
     * @param boolean $useSimpleBackendView
     * @return void
     */
    public function setUseSimpleBackendView($useSimpleBackendView)
    {
        $this->useSimpleBackendView = $useSimpleBackendView;
    }

    /**
     * Get BackendViewHeader
     *
     * @return string
     */
    public function getBackendViewHeader()
    {
        return $this->backendViewHeader;
    }

    /**
     * Set BackendViewHeader
     *
     * @param string $backendViewHeader
     * @return void
     */
    public function setBackendViewHeader($backendViewHeader)
    {
        $this->backendViewHeader = $backendViewHeader;
    }

    /**
     * Get BackendViewBodytext
     *
     * @return string
     */
    public function getBackendViewBodytext()
    {
        return $this->backendViewBodytext;
    }

    /**
     * Get BackendViewBodytext as array
     *
     * @return array
     */
    public function getBackendViewBodytextArray()
    {
        return GeneralUtility::trimExplode(',', $this->getBackendViewBodytext(), true);
    }

    /**
     * Set BackendViewBodytext
     *
     * @param string $backendViewBodytext
     * @return void
     */
    public function setBackendViewBodytext($backendViewBodytext)
    {
        $this->backendViewBodytext = $backendViewBodytext;
    }

    /**
     * @return string
     */
    public function getPreviewTemplateType()
    {
        return $this->previewTemplateType;
    }

    /**
     * @param string $previewTemplateType
     * @return void
     */
    public function setPreviewTemplateType($previewTemplateType)
    {
        $this->previewTemplateType = $previewTemplateType;
    }

    /**
     * @return string
     */
    public function getHeaderPreview()
    {
        return $this->headerPreview;
    }

    /**
     * @param string $headerPreview
     * @return void
     */
    public function setHeaderPreview($headerPreview)
    {
        $this->headerPreview = $headerPreview;
    }

    /**
     * @return string
     */
    public function getHeaderPreviewTemplateFile()
    {
        return $this->headerPreviewTemplateFile;
    }

    /**
     * @param string $headerPreviewTemplateFile
     * @return void
     */
    public function setHeaderPreviewTemplateFile($headerPreviewTemplateFile)
    {
        $this->headerPreviewTemplateFile = $headerPreviewTemplateFile;
    }

    /**
     * @return string
     */
    public function getBodytextPreview()
    {
        return $this->bodytextPreview;
    }

    /**
     * @param string $bodytextPreview
     * @return void
     */
    public function setBodytextPreview($bodytextPreview)
    {
        $this->bodytextPreview = $bodytextPreview;
    }

    /**
     * @return string
     */
    public function getBodytextPreviewTemplateFile()
    {
        return $this->bodytextPreviewTemplateFile;
    }

    /**
     * @param string $bodytextPreviewTemplateFile
     * @return void
     */
    public function setBodytextPreviewTemplateFile($bodytextPreviewTemplateFile)
    {
        $this->bodytextPreviewTemplateFile = $bodytextPreviewTemplateFile;
    }

    /**
     * @return bool
     */
    public function getEnableDetailpage()
    {
        return $this->enableDetailpage;
    }

    /**
     * @param bool $enableDetailpage
     * @return void
     */
    public function setEnableDetailpage($enableDetailpage)
    {
        $this->enableDetailpage = $enableDetailpage;
    }

    /**
     * @return string
     */
    public function getDetailpageIdentifier()
    {
        return $this->detailpageIdentifier;
    }

    /**
     * @param string $detailpageIdentifier
     * @return void
     */
    public function setDetailpageIdentifier($detailpageIdentifier)
    {
        $this->detailpageIdentifier = $detailpageIdentifier;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplateType()
    {
        return $this->detailpageTemplateType;
    }

    /**
     * @param string $detailpageTemplateType
     * @return void
     */
    public function setDetailpageTemplateType($detailpageTemplateType)
    {
        $this->detailpageTemplateType = $detailpageTemplateType;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplate()
    {
        return $this->detailpageTemplate;
    }

    /**
     * @param string $detailpageTemplate
     * @return void
     */
    public function setDetailpageTemplate($detailpageTemplate)
    {
        $this->detailpageTemplate = $detailpageTemplate;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplateFile()
    {
        return $this->detailpageTemplateFile;
    }

    /**
     * @param string $detailpageTemplateFile
     * @return void
     */
    public function setDetailpageTemplateFile($detailpageTemplateFile)
    {
        $this->detailpageTemplateFile = $detailpageTemplateFile;
    }

    /**
     * @return bool
     */
    public function getEnableContainer()
    {
        return $this->enableContainer;
    }

    /**
     * @param bool $enableContainer
     * @return void
     */
    public function setEnableContainer($enableContainer)
    {
        $this->enableContainer = $enableContainer;
    }

    /**
     * Get ContainerLimit
     *
     * @return int
     */
    public function getContainerItemLimit()
    {
        return $this->containerItemLimit;
    }

    /**
     * Set ContainerLimit
     *
     * @param int $containerItemLimit
     * @return void
     */
    public function setContainerItemLimit($containerItemLimit)
    {
        $this->containerItemLimit = $containerItemLimit;
    }

    /**
     * @return string
     */
    public function getContainerTemplateType()
    {
        return $this->containerTemplateType;
    }

    /**
     * @param string $containerTemplateType
     * @return void
     */
    public function setContainerTemplateType($containerTemplateType)
    {
        $this->containerTemplateType = $containerTemplateType;
    }

    /**
     * @return string
     */
    public function getContainerTemplate()
    {
        return $this->containerTemplate;
    }

    /**
     * @param string $containerTemplate
     * @return void
     */
    public function setContainerTemplate($containerTemplate)
    {
        $this->containerTemplate = $containerTemplate;
    }

    /**
     * @return string
     */
    public function getContainerTemplateFile()
    {
        return $this->containerTemplateFile;
    }

    /**
     * @param string $containerTemplateFile
     * @return void
     */
    public function setContainerTemplateFile($containerTemplateFile)
    {
        $this->containerTemplateFile = $containerTemplateFile;
    }

    /**
     * @return boolean
     */
    public function isWizardEnable()
    {
        return $this->wizardEnable;
    }

    /**
     * @param boolean $wizardEnable
     */
    public function setWizardEnable($wizardEnable)
    {
        $this->wizardEnable = $wizardEnable;
    }

    /**
     * @return string
     */
    public function getWizardCategory()
    {
        return $this->wizardCategory;
    }

    /**
     * @param string $wizardCategory
     */
    public function setWizardCategory($wizardCategory)
    {
        $this->wizardCategory = $wizardCategory;
    }

    /**
     * @return string
     */
    public function getWizardDescription()
    {
        return $this->wizardDescription;
    }

    /**
     * @param string $wizardDescription
     */
    public function setWizardDescription($wizardDescription)
    {
        $this->wizardDescription = $wizardDescription;
    }

    /**
     * @return string
     */
    public function getWizardIcon()
    {
        $wizardIcon = $this->wizardIcon;
        if (empty($wizardIcon)) {
            return 'regular_text';
        }
        return $wizardIcon;
    }

    /**
     * @param string $wizardIcon
     */
    public function setWizardIcon($wizardIcon)
    {
        $this->wizardIcon = $wizardIcon;
    }

    /**
     * @return string
     */
    public function getWizardCustomIcon()
    {
        return $this->wizardCustomIcon;
    }

    /**
     * @param string $wizardCustomIcon
     */
    public function setWizardCustomIcon($wizardCustomIcon)
    {
        $this->wizardCustomIcon = $wizardCustomIcon;
    }

    /**
     * @return string
     */
    public function getSelectedWizardIconPath()
    {
        if ($this->getWizardIcon() === 'custom') {
            return $this->getWizardCustomIcon();
        }
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.4')) {
            // TODO: Remove this when TYPO3 6.2 is outdated
            return 'typo3/sysext/t3skin/icons/gfx/c_wiz/' . $this->getWizardIcon() . '.gif';
        }
        return $this->getWizardIcon();
    }

    /**
     * Checks attached fields for given variable and returns the single field if found.
     * If not found, returns NULL.
     *
     * @param string $variable
     * @return NULL|DceField
     */
    public function getFieldByVariable($variable)
    {
        foreach ($this->getFields() as $field) {
            if ($field->getVariable() === $variable) {
                return $field;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getContentObject()
    {
        return $this->_contentObject;
    }

    /**
     * @param array $contentObject
     * @return void
     */
    public function setContentObject($contentObject)
    {
        $this->_contentObject = $contentObject;
    }

    /**
     * Renders the default DCE output
     *
     * @return string rendered output
     */
    public function render()
    {
        return $this->renderFluidTemplate();
    }

    /**
     * Renders the DCE detail page output
     *
     * @return string rendered output
     */
    public function renderDetailpage()
    {
        return $this->renderFluidTemplate(self::TEMPLATE_FIELD_DETAILPAGE);
    }

    /**
     * Renders the HeaderPreview output
     *
     * @return string rendered output
     */
    public function renderHeaderPreview()
    {
        return $this->renderFluidTemplate(self::TEMPLATE_FIELD_HEADERPREVIEW);
    }

    /**
     * Renders the BodytextPreview output
     *
     * @return string rendered output
     */
    public function renderBodytextPreview()
    {
        return $this->renderFluidTemplate(self::TEMPLATE_FIELD_BODYTEXTPREVIEW);
    }

    /**
     * Creates and renders fluid template
     *
     * @param int $templateType
     * @return string Rendered and trimmed template
     */
    protected function renderFluidTemplate($templateType = self::TEMPLATE_FIELD_DEFAULT)
    {
        $fluidTemplate = $this->getFluidStandaloneView($templateType);

        $fluidTemplate->assign('contentObject', $this->getContentObject());

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
    protected function getFieldsAsArray()
    {
        $contentObject = $this->getContentObject();
        if (array_key_exists($contentObject['uid'], static::$fieldsCache)) {
            return static::$fieldsCache[$contentObject['uid']];
        }
        $fields = array();
        /** @var $field \ArminVieweg\Dce\Domain\Model\DceField */
        foreach ($this->getFields() as $field) {
            if ($field->isTab()) {
                continue;
            }
            if ($field->hasSectionFields()) {
                /** @var $sectionField \ArminVieweg\Dce\Domain\Model\DceField */
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
        static::$fieldsCache[$contentObject['uid']] = $fields;
        return $fields;
    }

    /**
     * Checks if this DCE has fields, which map their values to TCA columns
     *
     * @return bool
     */
    public function getHasTcaMappings()
    {
        /** @var DceField $field */
        foreach ($this->getFields() as $field) {
            $mapTo = $field->getMapTo();
            if (!empty($mapTo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if this DCE adds new fields to TCA of tt_content
     *
     * @return bool
     */
    public function getAddsNewFieldsToTca()
    {
        /** @var DceField $field */
        foreach ($this->getFields() as $field) {
            $newTcaFieldName = $field->getNewTcaFieldName();
            if ($field->getMapTo() === '*newcol' && !empty($newTcaFieldName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if this DCE has some actions which can be performed
     *
     * @return bool
     */
    public function getHasActions()
    {
        if (!$this->getUseSimpleBackendView()) {
            return !$this->getHidden();
        }
        if ($this->getHasTcaMappings()) {
            return !$this->getHidden();
        }
        return false;
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
    public function __call($name, array $arguments)
    {
        if (substr($name, 0, 3) === 'get' && strlen($name) > 3) {
            $variable = lcfirst(substr($name, 3));
            if (method_exists($this, $variable)) {
                return $this->$variable();
            }

            $field = $this->getFieldByVariable($variable);
            if ($field instanceof DceField) {
                if ($field->isSection()) {
                    $fieldsArray = $this->getFieldsAsArray();
                    if (array_key_exists($variable, $fieldsArray)) {
                        return $fieldsArray[$variable];
                    }
                } else {
                    return $field->getValue();
                }
            }
        }
        return null;
    }

    /**
     * Creates new standalone view or returns cached one, if existing
     *
     * @param int $templateType
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    public function getFluidStandaloneView($templateType)
    {
        if (isset(self::$fluidTemplateCache[$this->getUid()][$templateType])) {
            return self::$fluidTemplateCache[$this->getUid()][$templateType];
        }

        $templateFields = $this->templateFields[$templateType];
        $typeGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['type']));

        /** @var $fluidTemplate \TYPO3\CMS\Fluid\View\StandaloneView */
        $fluidTemplate = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
        if ($this->$typeGetter() === 'inline') {
            $inlineTemplateGetter = 'get' . ucfirst(
                GeneralUtility::underscoredToLowerCamelCase($templateFields['inline'])
            );
            $fluidTemplate->setTemplateSource($this->$inlineTemplateGetter() . ' ');
        } else {
            $fileTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['file']));
            $filePath = File::getFilePath($this->$fileTemplateGetter());
            if (!file_exists($filePath)) {
                $fluidTemplate->setTemplateSource('');
            } else {
                $templateContent = file_get_contents($filePath);
                $fluidTemplate->setTemplateSource($templateContent . ' ');
            }
        }

        $fluidTemplate->setLayoutRootPath(File::getFilePath($this->getTemplateLayoutRootPath()));
        $fluidTemplate->setPartialRootPath(File::getFilePath($this->getTemplatePartialRootPath()));

        if ($templateType !== self::TEMPLATE_FIELD_CONTAINER) {
            $fluidTemplate->assign('dce', $this);
        }

        if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
            $fluidTemplate->assign('TSFE', $GLOBALS['TSFE']);
            $fluidTemplate->assign('page', $GLOBALS['TSFE']->page);

            $typoScriptService = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Service\TypoScriptService');
            $fluidTemplate->assign(
                'tsSetup',
                $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup)
            );
        }

        self::$fluidTemplateCache[$this->getUid()][$templateType] = $fluidTemplate;
        return $fluidTemplate;
    }

    /**
     * Get content element rows based on this DCE
     *
     * @return array|NULL
     */
    public function getRelatedContentElementRows()
    {
        if (array_key_exists($this->getUid(), static::$fieldsCache)) {
            return static::$fieldsCache[$this->getUid()];
        }
        $rows = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tt_content',
            'CType="dce_dceuid' . $this->getUid() . '" AND deleted=0',
            '',
            '',
            '',
            'uid'
        );
        static::$fieldsCache[$this->getUid()] = $rows;
        return $rows;
    }
}
