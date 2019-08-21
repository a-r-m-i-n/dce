<?php
namespace T3\Dce\Domain\Model;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Model for DCE fields. Contains configuration of fields and fetched values.
 * These fields are part of the DCE model.
 */
class DceField extends AbstractEntity
{
    /* Field Type: Element */
    public const TYPE_ELEMENT = 0;
    /* Field Type: Tab */
    public const TYPE_TAB = 1;
    /* Field Type: Section */
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
     * TCA column name to map $this->_value to
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
     * Constructor
     */
    public function __construct()
    {
        $this->sectionFields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = $type;
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
     * @return string
     */
    public function getVariable(): string
    {
        return $this->variable;
    }

    /**
     * @param string $variable
     * @return self
     */
    public function setVariable(string $variable): self
    {
        $this->variable = $variable;
        return $this;
    }

    /**
     * Returns field configuration as xml string.
     * Also it replaces the string "{$variable}" with the actual variable name of this field (used in FAL config).
     *
     * @return string
     */
    public function getConfiguration(): string
    {
        return str_replace('{$variable}', $this->getVariable(), $this->configuration);
    }

    /**
     * @return array
     */
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
     * @return self
     */
    public function setConfiguration(string $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getMapTo(): string
    {
        return $this->mapTo;
    }

    /**
     * @param string $mapTo
     * @return self
     */
    public function setMapTo(string $mapTo): self
    {
        $this->mapTo = $mapTo;
        return $this;
    }


    /**
     * @return string
     */
    public function getNewTcaFieldName(): string
    {
        return $this->newTcaFieldName;
    }

    /**
     * @param string $newTcaFieldName
     * @return self
     */
    public function setNewTcaFieldName(string $newTcaFieldName): self
    {
        $this->newTcaFieldName = $newTcaFieldName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewTcaFieldType(): string
    {
        return $this->newTcaFieldType;
    }

    /**
     * @param string $newTcaFieldType
     * @return self
     */
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
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Checks if section field count is greater than zero
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

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields
     * @return self
     */
    public function setSectionFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields): self
    {
        $this->sectionFields = $sectionFields;
        return $this;
    }

    /**
     * @param DceField $sectionField
     * @return self
     */
    public function addSectionField(DceField $sectionField) : self
    {
        $this->sectionFields->attach($sectionField);
        return $this;
    }

    /**
     * @param DceField $sectionField
     * @return self
     */
    public function removeSectionField(DceField $sectionField) : self
    {
        $this->sectionFields->detach($sectionField);
        return $this;
    }

    /**
     * Checks attached sectionFields for given variable and returns the single field if found.
     * If not found, returns null.
     *
     * @param string $variable
     * @return DceField|null
     */
    public function getSectionFieldByVariable($variable) : ?DceField
    {
        $sectionFields = $this->getSectionFields();
        if (isset($sectionFields)) {
            /** @var $sectionField DceField */
            foreach ($this->getSectionFields() as $sectionField) {
                if ($sectionField->getVariable() === $variable) {
                    return $sectionField;
                }
            }
        }
        return null;
    }

    /**
     * Get ParentDce
     *
     * @return Dce|null
     */
    public function getParentDce() : ?Dce
    {
        return $this->parentDce;
    }

    /**
     * Set ParentDce
     *
     * @param Dce $parentDce
     * @return self
     */
    public function setParentDce(Dce $parentDce) : self
    {
        $this->parentDce = $parentDce;
        return $this;
    }

    /**
     * Get ParentField
     *
     * @return DceField|null
     */
    public function getParentField() : ?DceField
    {
        return $this->parentField;
    }

    /**
     * Set ParentField
     *
     * @param DceField $parentField
     * @return self
     */
    public function setParentField(DceField $parentField) : self
    {
        $this->parentField = $parentField;
        return $this;
    }

    /**
     * Checks if the field is of type element
     *
     * @return bool
     */
    public function isElement() : bool
    {
        return ($this->getType() === self::TYPE_ELEMENT);
    }

    /**
     * Checks if the field is of type section
     *
     * @return bool
     */
    public function isSection() : bool
    {
        return $this->getType() === self::TYPE_SECTION;
    }

    /**
     * Checks if the field is of type tab
     *
     * @return bool
     */
    public function isTab() : bool
    {
        return ($this->getType() === self::TYPE_TAB);
    }

    /**
     * Checks if given xml configuration refers to FAL
     *
     * @return bool
     */
    public function isFal() : bool
    {
        $configuration = $this->getConfigurationAsArray();
        $configuration = $configuration['config'];
        return $configuration['type'] === 'inline' && $configuration['foreign_table'] === 'sys_file_reference';
    }
}
