<?php

namespace T3\Dce\Domain\Model;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Model for DCE fields. Contains configuration of fields and fetched values.
 * These fields are part of the DCE model.
 */
class DceField extends AbstractEntity
{
    // Field Type: Element
    public const TYPE_ELEMENT = 0;
    // Field Type: Tab
    public const TYPE_TAB = 1;
    // Field Type: Section
    public const TYPE_SECTION = 2;

    /**
     * @var int
     */
    protected $type = self::TYPE_ELEMENT;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $variable = '';

    /**
     * @var string
     */
    protected $configuration = '';

    /**
     * TCA column name to map $this->_value to.
     *
     * @var string
     */
    protected $mapTo = '';

    /**
     * @var string
     */
    protected $newTcaFieldName = '';

    /**
     * @var string
     */
    protected $newTcaFieldType = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\Dce\Domain\Model\DceField>
     */
    protected $sectionFields;

    /**
     * @var \T3\Dce\Domain\Model\Dce
     */
    protected $parentDce;

    /**
     * @var \T3\Dce\Domain\Model\DceField
     */
    protected $parentField;

    /**
     * @var string not persisted
     */
    protected $value = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sectionFields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getVariable(): string
    {
        return $this->variable;
    }

    public function setVariable(string $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Returns field configuration as xml string.
     * Also it replaces the string "{$variable}" with the actual variable name of this field (used in FAL config).
     */
    public function getConfiguration(): string
    {
        return str_replace('{$variable}', $this->getVariable(), $this->configuration);
    }

    public function getConfigurationAsArray(): array
    {
        $configuration = '<dceFieldConfiguration>' . $this->getConfiguration() . '</dceFieldConfiguration>';
        $configurationArray = GeneralUtility::xml2array($configuration);
        if (array_key_exists('dceFieldConfiguration', $configurationArray)) {
            return $configurationArray['dceFieldConfiguration'];
        }

        return $configurationArray;
    }

    /**
     * @param string $configuration xml string
     */
    public function setConfiguration(string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getMapTo(): string
    {
        return $this->mapTo;
    }

    public function setMapTo(string $mapTo): self
    {
        $this->mapTo = $mapTo;

        return $this;
    }

    public function getNewTcaFieldName(): string
    {
        return $this->newTcaFieldName;
    }

    public function setNewTcaFieldName(string $newTcaFieldName): self
    {
        $this->newTcaFieldName = $newTcaFieldName;

        return $this;
    }

    public function getNewTcaFieldType(): string
    {
        return $this->newTcaFieldType;
    }

    public function setNewTcaFieldType(string $newTcaFieldType): self
    {
        $this->newTcaFieldType = $newTcaFieldType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Checks if section field count is greater than zero.
     *
     * @return bool Returns TRUE when section fields existing, otherwise returns FALSE
     */
    public function hasSectionFields(): bool
    {
        $sectionFields = $this->getSectionFields();

        return isset($sectionFields) && \count($sectionFields) > 0;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\Dce\Domain\Model\DceField>|null
     */
    public function getSectionFields(): ?\TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->sectionFields;
    }

    public function setSectionFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields): self
    {
        $this->sectionFields = $sectionFields;

        return $this;
    }

    public function addSectionField(DceField $sectionField): self
    {
        $this->sectionFields->attach($sectionField);

        return $this;
    }

    public function removeSectionField(DceField $sectionField): self
    {
        $this->sectionFields->detach($sectionField);

        return $this;
    }

    /**
     * Checks attached sectionFields for given variable and returns the single field if found.
     * If not found, returns null.
     *
     * @param string $variable
     */
    public function getSectionFieldByVariable($variable): ?DceField
    {
        $sectionFields = $this->getSectionFields();
        if (isset($sectionFields)) {
            /** @var DceField $sectionField */
            foreach ($this->getSectionFields() as $sectionField) {
                if ($sectionField->getVariable() === $variable) {
                    return $sectionField;
                }
            }
        }

        return null;
    }

    /**
     * Get ParentDce.
     */
    public function getParentDce(): ?Dce
    {
        return $this->parentDce;
    }

    /**
     * Set ParentDce.
     */
    public function setParentDce(Dce $parentDce): self
    {
        $this->parentDce = $parentDce;

        return $this;
    }

    /**
     * Get ParentField.
     */
    public function getParentField(): ?DceField
    {
        return $this->parentField;
    }

    /**
     * Set ParentField.
     */
    public function setParentField(DceField $parentField): self
    {
        $this->parentField = $parentField;

        return $this;
    }

    /**
     * Checks if the field is of type element.
     */
    public function isElement(): bool
    {
        return self::TYPE_ELEMENT === $this->getType();
    }

    /**
     * Checks if the field is of type section.
     */
    public function isSection(): bool
    {
        return self::TYPE_SECTION === $this->getType();
    }

    /**
     * Checks if the field is of type tab.
     */
    public function isTab(): bool
    {
        return self::TYPE_TAB === $this->getType();
    }

    /**
     * Checks if given xml configuration refers to FAL.
     */
    public function isFal(): bool
    {
        $configuration = $this->getConfigurationAsArray();
        $configuration = $configuration['config'];

        return 'inline' === $configuration['type'] && 'sys_file_reference' === $configuration['foreign_table'];
    }
}
