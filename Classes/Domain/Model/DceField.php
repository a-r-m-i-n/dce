<?php
namespace ArminVieweg\Dce\Domain\Model;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Model for DCE fields. Contains configuration of fields and fetched values.
 * These fields are part of the DCE model.
 *
 * @package ArminVieweg\Dce
 */
class DceField extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /** Field Type: Element */
    const TYPE_ELEMENT = 0;
    /** Field Type: Tab */
    const TYPE_TAB = 1;
    /** Field Type: Section */
    const TYPE_SECTION = 2;

    /** @var int */
    protected $type;

    /** @var string */
    protected $title = '';

    /** @var string */
    protected $variable = '';

    /** @var string */
    protected $configuration = '';

    /**
     * TCA column name to map $this->_value to
     * @var string
     */
    protected $mapTo = '';

    /** @var string */
    protected $newTcaFieldName = '';

    /** @var string */
    protected $newTcaFieldType = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\ArminVieweg\Dce\Domain\Model\DceField>
     */
    protected $sectionFields = null;

    /**
     * @var \ArminVieweg\Dce\Domain\Model\Dce
     */
    protected $parentDce;

    /**
     * @var \ArminVieweg\Dce\Domain\Model\DceField
     */
    protected $parentField;

    /** @var string */
    protected $_value = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->sectionFields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->type;
    }

    /**
     * @param int $type
     * @return void
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
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @param string $variable
     * @return void
     */
    public function setVariable($variable)
    {
        $this->variable = $variable;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns field configuration as array
     *
     * @return array
     */
    public function getConfigurationAsArray()
    {
        $configuration = '<dceFieldConfiguration>' . $this->getConfiguration() . '</dceFieldConfiguration>';
        $configurationArray = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($configuration);
        if (array_key_exists('dceFieldConfiguration', $configurationArray)) {
            return $configurationArray['dceFieldConfiguration'];
        }
        return $configurationArray;
    }

    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get MapTo
     *
     * @return string
     */
    public function getMapTo()
    {
        return $this->mapTo;
    }

    /**
     * Set MapTo
     *
     * @param string $mapTo
     * @return void
     */
    public function setMapTo($mapTo)
    {
        $this->mapTo = $mapTo;
    }


    /**
     * Get NewTcaFieldName
     *
     * @return string
     */
    public function getNewTcaFieldName()
    {
        return $this->newTcaFieldName;
    }

    /**
     * Set NewTcaFieldName
     *
     * @param string $newTcaFieldName
     * @return void
     */
    public function setNewTcaFieldName($newTcaFieldName)
    {
        $this->newTcaFieldName = $newTcaFieldName;
    }

    /**
     * Get NewTcaFieldType
     *
     * @return string
     */
    public function getNewTcaFieldType()
    {
        return $this->newTcaFieldType;
    }

    /**
     * Set NewTcaFieldType
     *
     * @param string $newTcaFieldType
     * @return void
     */
    public function setNewTcaFieldType($newTcaFieldType)
    {
        $this->newTcaFieldType = $newTcaFieldType;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Checks if section field count is greater than zero
     *
     * @return bool Returns TRUE when section fields existing, otherwise returns FALSE
     */
    public function hasSectionFields()
    {
        $sectionFields = $this->getSectionFields();
        return isset($sectionFields) && count($sectionFields) > 0;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\ArminVieweg\Dce\Domain\Model\DceField>
     */
    public function getSectionFields()
    {
        return $this->sectionFields;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields
     * @return void
     */
    public function setSectionFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields)
    {
        $this->sectionFields = $sectionFields;
    }

    /**
     * Adds a section field
     *
     * @param DceField $sectionField
     * @return void
     */
    public function addSectionField(DceField $sectionField)
    {
        $this->sectionFields->attach($sectionField);
    }

    /**
     * Removes a section field
     *
     * @param DceField $sectionField
     * @return void
     */
    public function removeSectionField(DceField $sectionField)
    {
        $this->sectionFields->detach($sectionField);
    }

    /**
     * Checks attached sectionFields for given variable and returns the single field if found.
     * If not found, returns null.
     *
     * @param string $variable
     * @return DceField|null
     */
    public function getSectionFieldByVariable($variable)
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
     * @return Dce
     */
    public function getParentDce()
    {
        return $this->parentDce;
    }

    /**
     * Set ParentDce
     *
     * @param Dce $parentDce
     * @return void
     */
    public function setParentDce($parentDce)
    {
        $this->parentDce = $parentDce;
    }

    /**
     * Get ParentField
     *
     * @return DceField
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * Set ParentField
     *
     * @param DceField $parentField
     * @return void
     */
    public function setParentField($parentField)
    {
        $this->parentField = $parentField;
    }

    /**
     * Checks if the field is of type element
     *
     * @return bool
     */
    public function isElement()
    {
        return ($this->getType() === self::TYPE_ELEMENT);
    }

    /**
     * Checks if the field is of type section
     *
     * @return bool
     */
    public function isSection()
    {
        return $this->getType() === self::TYPE_SECTION;
    }

    /**
     * Checks if the field is of type tab
     *
     * @return bool
     */
    public function isTab()
    {
        return ($this->getType() === self::TYPE_TAB);
    }

    /**
     * Checks if given xml configuration refers to FAL
     *
     * @return bool
     */
    public function isFal()
    {
        $configuration = $this->getConfigurationAsArray();
        $configuration = $configuration['config'];
        return $configuration['type'] === 'inline' && $configuration['foreign_table'] === 'sys_file_reference';
    }
}
