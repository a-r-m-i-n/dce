<?php

namespace T3\Dce\Domain\Model;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\DetailPage\PageTitleProvider;
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
     * When this DCE is located inside of a DceContainer this attribute contains its current position.
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

    /** @var string
     */
    protected $backendViewHeaderExpression = '';

    /** @var bool
     */
    protected $backendViewHeaderUseExpression = false;

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
    protected $detailpageSlugExpression = '';

    /**
     * @var string
     */
    protected $detailpageTitleExpression = '';

    /**
     * @var string Allowed values: '', 'overwrite', 'prepend' or 'append'
     */
    protected $detailpageUseSlugAsTitle = '';

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
     * Constructor.
     */
    public function __construct()
    {
        $this->fields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getContainerIterator(): ?array
    {
        return $this->containerIterator;
    }

    public function setContainerIterator(array $containerIterator = null): self
    {
        $this->containerIterator = $containerIterator;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns configured identifier with "dce_" prefix or fallback, using the uid of the DCE.
     */
    public function getIdentifier(): string
    {
        return empty($this->identifier) ? 'dce_dceuid' . $this->getUid() : 'dce_' . $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = strtolower($identifier);

        return $this;
    }

    public function getTemplateContent(): string
    {
        return $this->templateContent;
    }

    public function setTemplateContent(string $templateContent): self
    {
        $this->templateContent = $templateContent;

        return $this;
    }

    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    public function setTemplateFile(string $templateFile): self
    {
        $this->templateFile = $templateFile;

        return $this;
    }

    public function getTemplateType(): string
    {
        return $this->templateType;
    }

    public function setTemplateType(string $templateType): self
    {
        $this->templateType = $templateType;

        return $this;
    }

    public function getTemplateLayoutRootPath(): string
    {
        return $this->templateLayoutRootPath;
    }

    public function setTemplateLayoutRootPath(string $templateLayoutRootPath): self
    {
        $this->templateLayoutRootPath = $templateLayoutRootPath;

        return $this;
    }

    public function getTemplatePartialRootPath(): string
    {
        return $this->templatePartialRootPath;
    }

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

    public function setFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param \T3\Dce\Domain\Model\DceField $field The field to be added
     */
    public function addField(DceField $field): self
    {
        $this->fields->attach($field);

        return $this;
    }

    /**
     * @param \T3\Dce\Domain\Model\DceField $fieldToRemove The field to be removed
     */
    public function removeField(DceField $fieldToRemove): self
    {
        $this->fields->detach($fieldToRemove);

        return $this;
    }

    public function getUseSimpleBackendView(): bool
    {
        return $this->useSimpleBackendView;
    }

    public function isUseSimpleBackendView(): bool
    {
        return $this->useSimpleBackendView;
    }

    public function setUseSimpleBackendView(bool $useSimpleBackendView): self
    {
        $this->useSimpleBackendView = $useSimpleBackendView;

        return $this;
    }

    public function getBackendViewHeader(): string
    {
        return $this->backendViewHeader;
    }

    public function setBackendViewHeader(string $backendViewHeader): self
    {
        $this->backendViewHeader = $backendViewHeader;

        return $this;
    }

    public function getBackendViewHeaderExpression(): string
    {
        return $this->backendViewHeaderExpression;
    }

    public function setBackendViewHeaderExpression(string $backendViewHeaderExpression): self
    {
        $this->backendViewHeaderExpression = $backendViewHeaderExpression;

        return $this;
    }

    public function isBackendViewHeaderUseExpression(): bool
    {
        return $this->backendViewHeaderUseExpression;
    }

    public function setBackendViewHeaderUseExpression(bool $backendViewHeaderUseExpression): self
    {
        $this->backendViewHeaderUseExpression = $backendViewHeaderUseExpression;

        return $this;
    }

    public function getBackendViewBodytext(): string
    {
        return $this->backendViewBodytext;
    }

    public function getBackendViewBodytextArray(): array
    {
        return GeneralUtility::trimExplode(',', $this->getBackendViewBodytext(), true) ?? [];
    }

    public function setBackendViewBodytext(string $backendViewBodytext): self
    {
        $this->backendViewBodytext = $backendViewBodytext;

        return $this;
    }

    public function getBackendTemplateType(): string
    {
        return $this->backendTemplateType;
    }

    public function setBackendTemplateType(string $backendTemplateType): self
    {
        $this->backendTemplateType = $backendTemplateType;

        return $this;
    }

    public function getBackendTemplateContent(): string
    {
        return $this->backendTemplateContent;
    }

    public function setBackendTemplateContent(string $backendTemplateContent): self
    {
        $this->backendTemplateContent = $backendTemplateContent;

        return $this;
    }

    public function getBackendTemplateFile(): string
    {
        return $this->backendTemplateFile;
    }

    public function setBackendTemplateFile(string $backendTemplateFile): self
    {
        $this->backendTemplateFile = $backendTemplateFile;

        return $this;
    }

    public function getEnableDetailpage(): bool
    {
        return $this->enableDetailpage;
    }

    public function setEnableDetailpage(bool $enableDetailpage): self
    {
        $this->enableDetailpage = $enableDetailpage;

        return $this;
    }

    public function getDetailpageIdentifier(): string
    {
        return $this->detailpageIdentifier;
    }

    public function setDetailpageIdentifier(string $detailpageIdentifier): self
    {
        $this->detailpageIdentifier = $detailpageIdentifier;

        return $this;
    }

    public function getDetailpageSlugExpression(): string
    {
        return $this->detailpageSlugExpression;
    }

    public function setDetailpageSlugExpression(string $detailpageSlugExpression): self
    {
        $this->detailpageSlugExpression = $detailpageSlugExpression;

        return $this;
    }

    public function getDetailpageTitleExpression(): string
    {
        return $this->detailpageTitleExpression;
    }

    public function setDetailpageTitleExpression(string $detailpageTitleExpression): self
    {
        $this->detailpageTitleExpression = $detailpageTitleExpression;

        return $this;
    }

    public function getDetailpageUseSlugAsTitle(): string
    {
        return $this->detailpageUseSlugAsTitle;
    }

    public function setDetailpageUseSlugAsTitle(string $detailpageUseSlugAsTitle): self
    {
        $this->detailpageUseSlugAsTitle = $detailpageUseSlugAsTitle;

        return $this;
    }

    public function getDetailpageTemplateType(): string
    {
        return $this->detailpageTemplateType;
    }

    public function setDetailpageTemplateType(string $detailpageTemplateType): self
    {
        $this->detailpageTemplateType = $detailpageTemplateType;

        return $this;
    }

    public function getDetailpageTemplate(): string
    {
        return $this->detailpageTemplate;
    }

    public function setDetailpageTemplate(string $detailpageTemplate): self
    {
        $this->detailpageTemplate = $detailpageTemplate;

        return $this;
    }

    public function getDetailpageTemplateFile(): string
    {
        return $this->detailpageTemplateFile;
    }

    public function setDetailpageTemplateFile(string $detailpageTemplateFile): self
    {
        $this->detailpageTemplateFile = $detailpageTemplateFile;

        return $this;
    }

    public function getEnableContainer(): bool
    {
        return $this->enableContainer;
    }

    public function setEnableContainer(bool $enableContainer): self
    {
        $this->enableContainer = $enableContainer;

        return $this;
    }

    public function getContainerItemLimit(): int
    {
        return $this->containerItemLimit;
    }

    public function setContainerItemLimit(int $containerItemLimit): self
    {
        $this->containerItemLimit = $containerItemLimit;

        return $this;
    }

    public function isContainerDetailAutohide(): bool
    {
        return $this->containerDetailAutohide;
    }

    public function setContainerDetailAutohide(bool $containerDetailAutohide): self
    {
        $this->containerDetailAutohide = $containerDetailAutohide;

        return $this;
    }

    public function getContainerTemplateType(): string
    {
        return $this->containerTemplateType;
    }

    public function setContainerTemplateType(string $containerTemplateType): self
    {
        $this->containerTemplateType = $containerTemplateType;

        return $this;
    }

    public function getContainerTemplate(): string
    {
        return $this->containerTemplate;
    }

    public function setContainerTemplate(string $containerTemplate): self
    {
        $this->containerTemplate = $containerTemplate;

        return $this;
    }

    public function getContainerTemplateFile(): string
    {
        return $this->containerTemplateFile;
    }

    public function setContainerTemplateFile(string $containerTemplateFile): self
    {
        $this->containerTemplateFile = $containerTemplateFile;

        return $this;
    }

    public function isWizardEnable(): bool
    {
        return $this->wizardEnable;
    }

    public function setWizardEnable(bool $wizardEnable): self
    {
        $this->wizardEnable = $wizardEnable;

        return $this;
    }

    public function getWizardCategory(): string
    {
        return $this->wizardCategory;
    }

    public function setWizardCategory(string $wizardCategory): self
    {
        $this->wizardCategory = $wizardCategory;

        return $this;
    }

    public function getWizardDescription(): string
    {
        return $this->wizardDescription;
    }

    public function setWizardDescription(string $wizardDescription): self
    {
        $this->wizardDescription = $wizardDescription;

        return $this;
    }

    public function getWizardIcon(): string
    {
        $wizardIcon = $this->wizardIcon;
        if (empty($wizardIcon)) {
            return 'regular_text';
        }

        return $wizardIcon;
    }

    public function setWizardIcon(string $wizardIcon): self
    {
        $this->wizardIcon = $wizardIcon;

        return $this;
    }

    public function getWizardCustomIcon(): string
    {
        return $this->wizardCustomIcon;
    }

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
        if ('custom' === $this->getWizardIcon()) {
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

    public function getContentObject(): array
    {
        return $this->contentObject;
    }

    public function setContentObject(array $contentObject): self
    {
        $this->contentObject = $contentObject;

        return $this;
    }

    /**
     * Renders the default DCE output
     * or the detail page output, if enabled and configured GET param is given.
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
     * Alias for render method.
     */
    public function getRender(): string
    {
        return $this->render();
    }

    /**
     * Checks if the display of detail page is triggered (by GET parameter in current request).
     * Always returns false, if detail page is not enabled for this DCE.
     */
    public function isDetailPageTriggered(): bool
    {
        if ($this->getEnableDetailpage()) {
            $detailUid = (int)GeneralUtility::_GP($this->getDetailpageIdentifier());

            return $detailUid && (int)$this->getContentObject()['uid'] === $detailUid;
        }

        return false;
    }

    /**
     * Renders the DCE detail page output.
     *
     * @return string rendered output
     */
    public function renderDetailpage(): string
    {
        if ($this->getDetailpageUseSlugAsTitle() && !empty($this->getDetailpageTitleExpression())) {
            /** @var PageTitleProvider $dceDetailPageTitleProvider */
            $dceDetailPageTitleProvider = GeneralUtility::makeInstance(PageTitleProvider::class);
            $dceDetailPageTitleProvider->generate($this);
        }

        return $this->renderFluidTemplate(DceTemplateTypes::DETAILPAGE);
    }

    /**
     * Alias for renderDetailpage method.
     */
    public function getRenderDetailpage(): string
    {
        return $this->renderDetailpage();
    }

    /**
     * Renders the DCE Backend Template.
     *
     * @param string $section If set just 'header' or 'bodytext' part is returned
     *
     * @return string|null rendered output
     */
    public function renderBackendTemplate(string $section = ''): ?string
    {
        $backendTemplateSeparator = '<dce-separator />';

        $fullBackendTemplate = $this->renderFluidTemplate(DceTemplateTypes::BACKEND_TEMPLATE);
        if (!empty($section)) {
            $backendTemplateParts = GeneralUtility::trimExplode($backendTemplateSeparator, $fullBackendTemplate);

            return 'bodytext' === $section ? $backendTemplateParts[1] : $backendTemplateParts[0];
        }

        return $fullBackendTemplate;
    }

    /**
     * Creates and renders fluid template.
     *
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
            'field' => $fields,
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
        /** @var DceField $field */
        foreach ($this->getFields() ?? [] as $field) {
            if ($field->isTab()) {
                continue;
            }
            if ($field->hasSectionFields()) {
                /** @var DceField $sectionField */
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
     * Checks if this DCE has fields, which map their values to TCA columns.
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
     * Checks if this DCE adds new fields to TCA of tt_content.
     */
    public function getAddsNewFieldsToTca(): bool
    {
        /** @var DceField $field */
        foreach ($this->getFields() ?? [] as $field) {
            $newTcaFieldName = $field->getNewTcaFieldName();
            if ('*newcol' === $field->getMapTo() && !empty($newTcaFieldName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get content element rows based on this DCE.
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
     * {dce.get.fieldname}.
     *
     * @return array key is field name, value is mixed
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
     *
     * @return mixed
     *
     * @deprecated Do not use "{dce.fieldname}" anymore to access field values of DCE object.
     *             Use "{dce.get.fieldname}" in your Fluid templates instead.
     * @see Dce::getGet()
     */
    public function __call($name, array $arguments)
    {
        if (0 === strpos($name, 'get') && \strlen($name) > 3) {
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
