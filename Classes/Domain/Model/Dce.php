<?php
namespace ArminVieweg\Dce\Domain\Model;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
    /** Identifier for backend template */
    const TEMPLATE_FIELD_BACKEND_TEMPLATE = 5;

    /**
     * @var array Cache for fluid instances
     */
    static protected $fluidTemplateCache = [];

    /**
     * @var array Cache for DceFields
     */
    static protected $fieldsCache = [];

    /**
     * @var array Cache for content element rows
     */
    static protected $contentElementRowsCache = [];

    /**
     * @var array Database field names of columns for different types of templates
     */
    protected $templateFields = [
        self::TEMPLATE_FIELD_DEFAULT => [
            'type' => 'template_type',
            'inline' => 'template_content',
            'file' => 'template_file'
        ],
        self::TEMPLATE_FIELD_DETAILPAGE => [
            'type' => 'detailpage_template_type',
            'inline' => 'detailpage_template',
            'file' => 'detailpage_template_file'
        ],
        self::TEMPLATE_FIELD_CONTAINER => [
            'type' => 'container_template_type',
            'inline' => 'container_template',
            'file' => 'container_template_file'
        ],
        self::TEMPLATE_FIELD_BACKEND_TEMPLATE => [
            'type' => 'backend_template_type',
            'inline' => 'backend_template_content',
            'file' => 'backend_template_file'
        ]
    ];

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\ArminVieweg\Dce\Domain\Model\DceField>
     */
    protected $fields = null;

    /**
     * When this DCE is located inside of a DceContainer this attribute contains its current position
     *
     * @var array|null
     */
    protected $containerIterator = null;

    /** @var bool */
    protected $hidden = false;

    /** @var string */
    protected $title = '';

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
    protected $backendTemplateType = '';

    /** @var string */
    protected $backendTemplateContent = '';

    /** @var string */
    protected $backendTemplateFile = '';

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

    /** @var bool */
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
    protected $_contentObject = [];


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
     * @return array|null
     */
    public function getContainerIterator()
    {
        return $this->containerIterator;
    }

    /**
     * @param array|null $containerIterator
     * @return void
     */
    public function setContainerIterator($containerIterator)
    {
        $this->containerIterator = $containerIterator;
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
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\ArminVieweg\Dce\Domain\Model\DceField>
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
    public function getBackendTemplateType()
    {
        return $this->backendTemplateType;
    }

    /**
     * @param string $backendTemplateType
     * @return void
     */
    public function setBackendTemplateType($backendTemplateType)
    {
        $this->backendTemplateType = $backendTemplateType;
    }

    /**
     * @return string
     */
    public function getBackendTemplateContent()
    {
        return $this->backendTemplateContent;
    }

    /**
     * @param string $backendTemplateContent
     * @return void
     */
    public function setBackendTemplateContent($backendTemplateContent)
    {
        $this->backendTemplateContent = $backendTemplateContent;
    }

    /**
     * @return string
     */
    public function getBackendTemplateFile()
    {
        return $this->backendTemplateFile;
    }

    /**
     * @param string $backendTemplateFile
     * @return void
     */
    public function setBackendTemplateFile($backendTemplateFile)
    {
        $this->backendTemplateFile = $backendTemplateFile;
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
        return $this->getWizardIcon();
    }

    /**
     * Checks attached fields for given variable and returns the single field if found.
     * If not found, returns NULL.
     *
     * @param string $variable
     * @return null|DceField
     */
    public function getFieldByVariable($variable)
    {
        /** @var DceField $field */
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
     * Renders the DCE Backend Template
     *
     * @param string $section If set just 'header' or 'bodytext' part is returned
     * @return string rendered output
     */
    public function renderBackendTemplate($section = '')
    {
        $backendTemplateSeparator = '<dce-separator />';

        $fullBackendTemplate = $this->renderFluidTemplate(self::TEMPLATE_FIELD_BACKEND_TEMPLATE);
        if (!empty($section)) {
            $backendTemplateParts = GeneralUtility::trimExplode($backendTemplateSeparator, $fullBackendTemplate);
            return $section === 'bodytext' ? $backendTemplateParts[1] : $backendTemplateParts[0];
        }
        return $fullBackendTemplate;
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

        $fields = $this->getFieldsAsArray();
        $variables = [
            'contentObject' => $this->getContentObject(),
            'fields' => $fields,
            'field' => $fields
        ];
        $fluidTemplate->assignMultiple($variables);

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
        $fields = [];
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
        $cacheKey = $this->getUid();
        if ($this->getEnableContainer()) {
            $containerIterator = $this->getContainerIterator();
            $cacheKey .= '-' . $containerIterator['index'];
        }

        if (isset(self::$fluidTemplateCache[$cacheKey][$templateType])) {
            return self::$fluidTemplateCache[$cacheKey][$templateType];
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

        $fluidTemplate->setLayoutRootPaths([File::getFilePath($this->getTemplateLayoutRootPath())]);
        $fluidTemplate->setPartialRootPaths([File::getFilePath($this->getTemplatePartialRootPath())]);

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
