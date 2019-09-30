<?php
namespace T3\Dce\Domain\Model;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\TemplateRenderer\DceTemplateTypes;
use T3\Dce\Components\TemplateRenderer\StandaloneViewFactory;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Model for DCEs. This model contains all necessary informations
 * to render the content element in frontend.
 */
class Dce extends AbstractEntity
{
    /**
     * @var array Cache for DceFields
     */
    protected static $fieldsCache = [];

    /**
     * @var array Cache for content element rows
     */
    protected static $contentElementRowsCache = [];

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\Dce\Domain\Model\DceField>
     */
    protected $fields;

    /**
     * When this DCE is located inside of a DceContainer this attribute contains its current position
     *
     * @var array|null
     */
    protected $containerIterator;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var string
     */
    protected $templateType = '';

    /**
     * @var string
     */
    protected $templateContent = '';

    /**
     * @var string
     */
    protected $templateFile = '';

    /**
     * @var string
     */
    protected $templateLayoutRootPath = '';

    /**
     * @var string
     */
    protected $templatePartialRootPath = '';

    /**
     * @var bool
     */
    protected $useSimpleBackendView = false;

    /** @var string
     */
    protected $backendViewHeader = '';

    /**
     * @var string
     */
    protected $backendViewBodytext = '';

    /**
     * @var string
     */
    protected $backendTemplateType = '';

    /**
     * @var string
     */
    protected $backendTemplateContent = '';

    /**
     * @var string
     */
    protected $backendTemplateFile = '';

    /**
     * @var bool
     */
    protected $enableDetailpage = false;

    /**
     * @var string
     */
    protected $detailpageIdentifier = '';

    /**
     * @var string
     */
    protected $detailpageTemplateType = '';

    /**
     * @var string
     */
    protected $detailpageTemplate = '';

    /**
     * @var string
     */
    protected $detailpageTemplateFile = '';

    /**
     * @var bool
     */
    protected $enableContainer = false;

    /**
     * @var int
     */
    protected $containerItemLimit = 0;

    /**
     * @var bool
     */
    protected $containerDetailAutohide = true;

    /**
     * @var string
     */
    protected $containerTemplateType = '';

    /**
     * @var string
     */
    protected $containerTemplate = '';

    /**
     * @var string
     */
    protected $containerTemplateFile = '';

    /**
     * @var bool
     */
    protected $wizardEnable = true;

    /**
     * @var string
     */
    protected $wizardCategory = '';

    /**
     * @var string
     */
    protected $wizardDescription = '';

    /**
     * @var string
     */
    protected $wizardIcon = '';

    /**
     * @var string
     */
    protected $wizardCustomIcon = '';

    /**
     * @var array not persisted
     */
    protected $contentObject = [];


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return bool
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     * @return self
     */
    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getContainerIterator(): ?array
    {
        return $this->containerIterator;
    }

    /**
     * @param array|null $containerIterator
     * @return self
     */
    public function setContainerIterator(array $containerIterator = null): self
    {
        $this->containerIterator = $containerIterator;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns configured identifier with "dce_" prefix or fallback, using the uid of the DCE
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return empty($this->identifier) ? 'dce_dceuid' . $this->getUid() : 'dce_' . $this->identifier;
    }

    /**
     * @param string $identifier
     * @return self
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = strtolower($identifier);
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateContent(): string
    {
        return $this->templateContent;
    }

    /**
     * @param string $templateContent
     * @return self
     */
    public function setTemplateContent(string $templateContent): self
    {
        $this->templateContent = $templateContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * @param string $templateFile
     * @return self
     */
    public function setTemplateFile(string $templateFile): self
    {
        $this->templateFile = $templateFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateType(): string
    {
        return $this->templateType;
    }

    /**
     * @param string $templateType
     * @return self
     */
    public function setTemplateType(string $templateType): self
    {
        $this->templateType = $templateType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateLayoutRootPath(): string
    {
        return $this->templateLayoutRootPath;
    }

    /**
     * @param string $templateLayoutRootPath
     * @return self
     */
    public function setTemplateLayoutRootPath(string $templateLayoutRootPath): self
    {
        $this->templateLayoutRootPath = $templateLayoutRootPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatePartialRootPath(): string
    {
        return $this->templatePartialRootPath;
    }

    /**
     * @param string $templatePartialRootPath
     * @return self
     */
    public function setTemplatePartialRootPath(string $templatePartialRootPath): self
    {
        $this->templatePartialRootPath = $templatePartialRootPath;
        return $this;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\Dce\Domain\Model\DceField>|null
     */
    public function getFields(): ?\TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->fields;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DceField> $fields
     * @return self
     */
    public function setFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param \T3\Dce\Domain\Model\DceField $field The field to be added
     * @return self
     */
    public function addField(\T3\Dce\Domain\Model\DceField $field): self
    {
        $this->fields->attach($field);
        return $this;
    }

    /**
     * @param \T3\Dce\Domain\Model\DceField $fieldToRemove The field to be removed
     * @return self
     */
    public function removeField(\T3\Dce\Domain\Model\DceField $fieldToRemove): self
    {
        $this->fields->detach($fieldToRemove);
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseSimpleBackendView(): bool
    {
        return $this->useSimpleBackendView;
    }

    /**
     * @return bool
     */
    public function isUseSimpleBackendView(): bool
    {
        return $this->useSimpleBackendView;
    }

    /**
     * @param bool $useSimpleBackendView
     * @return self
     */
    public function setUseSimpleBackendView(bool $useSimpleBackendView): self
    {
        $this->useSimpleBackendView = $useSimpleBackendView;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackendViewHeader(): string
    {
        return $this->backendViewHeader;
    }

    /**
     * @param string $backendViewHeader
     * @return self
     */
    public function setBackendViewHeader(string $backendViewHeader): self
    {
        $this->backendViewHeader = $backendViewHeader;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackendViewBodytext(): string
    {
        return $this->backendViewBodytext;
    }

    /**
     * @return array
     */
    public function getBackendViewBodytextArray(): array
    {
        return GeneralUtility::trimExplode(',', $this->getBackendViewBodytext(), true) ?? [];
    }

    /**
     * @param string $backendViewBodytext
     * @return self
     */
    public function setBackendViewBodytext(string $backendViewBodytext): self
    {
        $this->backendViewBodytext = $backendViewBodytext;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackendTemplateType(): string
    {
        return $this->backendTemplateType;
    }

    /**
     * @param string $backendTemplateType
     * @return self
     */
    public function setBackendTemplateType(string $backendTemplateType): self
    {
        $this->backendTemplateType = $backendTemplateType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackendTemplateContent(): string
    {
        return $this->backendTemplateContent;
    }

    /**
     * @param string $backendTemplateContent
     * @return self
     */
    public function setBackendTemplateContent(string $backendTemplateContent): self
    {
        $this->backendTemplateContent = $backendTemplateContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackendTemplateFile(): string
    {
        return $this->backendTemplateFile;
    }

    /**
     * @param string $backendTemplateFile
     * @return self
     */
    public function setBackendTemplateFile(string $backendTemplateFile): self
    {
        $this->backendTemplateFile = $backendTemplateFile;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableDetailpage(): bool
    {
        return $this->enableDetailpage;
    }

    /**
     * @param bool $enableDetailpage
     * @return self
     */
    public function setEnableDetailpage(bool $enableDetailpage): self
    {
        $this->enableDetailpage = $enableDetailpage;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailpageIdentifier(): string
    {
        return $this->detailpageIdentifier;
    }

    /**
     * @param string $detailpageIdentifier
     * @return self
     */
    public function setDetailpageIdentifier(string $detailpageIdentifier): self
    {
        $this->detailpageIdentifier = $detailpageIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplateType(): string
    {
        return $this->detailpageTemplateType;
    }

    /**
     * @param string $detailpageTemplateType
     * @return self
     */
    public function setDetailpageTemplateType(string $detailpageTemplateType): self
    {
        $this->detailpageTemplateType = $detailpageTemplateType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplate(): string
    {
        return $this->detailpageTemplate;
    }

    /**
     * @param string $detailpageTemplate
     * @return self
     */
    public function setDetailpageTemplate(string $detailpageTemplate): self
    {
        $this->detailpageTemplate = $detailpageTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailpageTemplateFile(): string
    {
        return $this->detailpageTemplateFile;
    }

    /**
     * @param string $detailpageTemplateFile
     * @return self
     */
    public function setDetailpageTemplateFile(string $detailpageTemplateFile): self
    {
        $this->detailpageTemplateFile = $detailpageTemplateFile;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableContainer(): bool
    {
        return $this->enableContainer;
    }

    /**
     * @param bool $enableContainer
     * @return self
     */
    public function setEnableContainer(bool $enableContainer): self
    {
        $this->enableContainer = $enableContainer;
        return $this;
    }

    /**
     * @return int
     */
    public function getContainerItemLimit(): int
    {
        return $this->containerItemLimit;
    }

    /**
     * @param int $containerItemLimit
     * @return self
     */
    public function setContainerItemLimit(int $containerItemLimit): self
    {
        $this->containerItemLimit = $containerItemLimit;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContainerDetailAutohide(): bool
    {
        return $this->containerDetailAutohide;
    }

    /**
     * @param bool $containerDetailAutohide
     * @return self
     */
    public function setContainerDetailAutohide(bool $containerDetailAutohide): self
    {
        $this->containerDetailAutohide = $containerDetailAutohide;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerTemplateType(): string
    {
        return $this->containerTemplateType;
    }

    /**
     * @param string $containerTemplateType
     * @return self
     */
    public function setContainerTemplateType(string $containerTemplateType): self
    {
        $this->containerTemplateType = $containerTemplateType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerTemplate(): string
    {
        return $this->containerTemplate;
    }

    /**
     * @param string $containerTemplate
     * @return self
     */
    public function setContainerTemplate(string $containerTemplate): self
    {
        $this->containerTemplate = $containerTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerTemplateFile(): string
    {
        return $this->containerTemplateFile;
    }

    /**
     * @param string $containerTemplateFile
     * @return self
     */
    public function setContainerTemplateFile(string $containerTemplateFile): self
    {
        $this->containerTemplateFile = $containerTemplateFile;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWizardEnable(): bool
    {
        return $this->wizardEnable;
    }

    /**
     * @param bool $wizardEnable
     * @return self
     */
    public function setWizardEnable(bool $wizardEnable): self
    {
        $this->wizardEnable = $wizardEnable;
        return $this;
    }

    /**
     * @return string
     */
    public function getWizardCategory(): string
    {
        return $this->wizardCategory;
    }

    /**
     * @param string $wizardCategory
     * @return self
     */
    public function setWizardCategory(string $wizardCategory): self
    {
        $this->wizardCategory = $wizardCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getWizardDescription(): string
    {
        return $this->wizardDescription;
    }

    /**
     * @param string $wizardDescription
     * @return self
     */
    public function setWizardDescription(string $wizardDescription): self
    {
        $this->wizardDescription = $wizardDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getWizardIcon(): string
    {
        $wizardIcon = $this->wizardIcon;
        if (empty($wizardIcon)) {
            return 'regular_text';
        }
        return $wizardIcon;
    }

    /**
     * @param string $wizardIcon
     * @return self
     */
    public function setWizardIcon(string $wizardIcon): self
    {
        $this->wizardIcon = $wizardIcon;
        return $this;
    }

    /**
     * @return string
     */
    public function getWizardCustomIcon(): string
    {
        return $this->wizardCustomIcon;
    }

    /**
     * @param string $wizardCustomIcon
     * @return self
     */
    public function setWizardCustomIcon(string $wizardCustomIcon): self
    {
        $this->wizardCustomIcon = $wizardCustomIcon;
        return $this;
    }

    /**
     * @return string name of selected wizard icon
     */
    public function getSelectedWizardIcon(): string
    {
        if ($this->getWizardIcon() === 'custom') {
            return $this->getWizardCustomIcon();
        }
        return $this->getWizardIcon();
    }

    /**
     * @return string path of selected wizard icon
     */
    public function getSelectedWizardIconPath(): string
    {
        return File::get($this->getSelectedWizardIcon());
    }

    /**
     * Checks attached fields for given variable and returns the single field if found.
     * If not found, returns null.
     *
     * @param string $variable
     * @return DceField|null
     */
    public function getFieldByVariable($variable): ?DceField
    {
        /** @var DceField $field */
        foreach ($this->getFields() ?? [] as $field) {
            if ($field->getVariable() === $variable) {
                return $field;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getContentObject(): array
    {
        return $this->contentObject;
    }

    /**
     * @param array $contentObject
     * @return self
     */
    public function setContentObject(array $contentObject): self
    {
        $this->contentObject = $contentObject;
        return $this;
    }

    /**
     * Renders the default DCE output
     * or the detail page output, if enabled and configured GET param is given
     *
     * @return string The rendered output
     */
    public function render(): string
    {
        if ($this->isDetailPageTriggered()) {
            return $this->renderDetailpage();
        }
        return $this->renderFluidTemplate();
    }

    /**
     * Checks if the display of detail page is triggered (by GET parameter in current request).
     * Always returns false, if detail page is not enabled for this DCE.
     *
     * @return bool
     */
    public function isDetailPageTriggered(): bool
    {
        if ($this->getEnableDetailpage()) {
            $detailUid = (int) GeneralUtility::_GP($this->getDetailpageIdentifier());
            return $detailUid && (int) $this->getContentObject()['uid'] === $detailUid;
        }
        return false;
    }

    /**
     * Alias for render method
     *
     * @return string
     */
    public function getRender(): string
    {
        return $this->render();
    }

    /**
     * Renders the DCE detail page output
     *
     * @return string rendered output
     */
    public function renderDetailpage(): string
    {
        return $this->renderFluidTemplate(DceTemplateTypes::DETAILPAGE);
    }

    /**
     * Renders the DCE Backend Template
     *
     * @param string $section If set just 'header' or 'bodytext' part is returned
     * @return string|null rendered output
     */
    public function renderBackendTemplate(string $section = ''): ?string
    {
        $backendTemplateSeparator = '<dce-separator />';

        $fullBackendTemplate = $this->renderFluidTemplate(DceTemplateTypes::BACKEND_TEMPLATE);
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
    protected function renderFluidTemplate(int $templateType = DceTemplateTypes::DEFAULT): string
    {
        $viewFactory = GeneralUtility::makeInstance(StandaloneViewFactory::class);
        $fluidTemplate = $viewFactory->getDceTemplateView($this, $templateType);

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
    protected function getFieldsAsArray(): array
    {
        $contentObject = $this->getContentObject();
        if (array_key_exists($contentObject['uid'], static::$fieldsCache)) {
            return static::$fieldsCache[$contentObject['uid']];
        }
        $fields = [];
        /** @var $field DceField */
        foreach ($this->getFields() ?? [] as $field) {
            if ($field->isTab()) {
                continue;
            }
            if ($field->hasSectionFields()) {
                /** @var $sectionField DceField */
                foreach ($field->getSectionFields() as $sectionField) {
                    $sectionFieldValues = $sectionField->getValue();
                    if (\is_array($sectionFieldValues)) {
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
    public function getHasTcaMappings(): bool
    {
        /** @var DceField $field */
        foreach ($this->getFields() ?? [] as $field) {
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
    public function getAddsNewFieldsToTca(): bool
    {
        /** @var DceField $field */
        foreach ($this->getFields() ?? [] as $field) {
            $newTcaFieldName = $field->getNewTcaFieldName();
            if ($field->getMapTo() === '*newcol' && !empty($newTcaFieldName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get content element rows based on this DCE
     *
     * @return array|null
     */
    public function getRelatedContentElementRows(): ?array
    {
        if (array_key_exists($this->getIdentifier(), static::$fieldsCache)) {
            return static::$fieldsCache[$this->getIdentifier()];
        }
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter($this->getIdentifier(), \PDO::PARAM_STR)
                )
            );
        $rows = DatabaseUtility::getRowsFromQueryBuilder($queryBuilder, 'uid');
        static::$fieldsCache[$this->getIdentifier()] = $rows;
        return $rows;
    }

    /**
     * This method provides access to field values of this DCE. Usage in your fluid templates:
     * {dce.get.fieldname}
     *
     * @return array Key is field name, value is mixed.
     */
    public function getGet(): array
    {
        return $this->getFieldsAsArray();
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
     * @deprecated Do not use "{dce.fieldname}" anymore to access field values of DCE object.
     *             Use "{dce.get.fieldname}" in your Fluid templates instead.
     * @see Dce::getGet()
     */
    public function __call($name, array $arguments)
    {
        if (strpos($name, 'get') === 0 && \strlen($name) > 3) {
            trigger_error(
                'Do not use "{dce.fieldname}" anymore to access field values of DCE object. ' .
                'Use "{dce.get.fieldname}" in your Fluid templates instead.',
                E_USER_DEPRECATED
            );

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
}
