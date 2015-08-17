<?php
namespace ArminVieweg\Dce\Domain\Repository;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Model\DceField;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * DCE repository
 *
 * @package ArminVieweg\Dce
 */
class DceRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Returns database DCEs and static DCEs as merged array
     *
     * @param bool $includeHidden
     * @return array
     */
    public function findAllAndStatics($includeHidden = false)
    {
        /** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
        $staticDceUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');
        $staticDces = $staticDceUtility->getAll();

        if ($includeHidden) {
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings $querySettings */
            $querySettings = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
            $querySettings->setIgnoreEnableFields(true);
            $this->setDefaultQuerySettings($querySettings);
        }
        $this->setDefaultOrderings(array('sorting' => QueryInterface::ORDER_ASCENDING));
        $databaseDces = $this->findAll()->toArray();
        return array_merge($databaseDces, $staticDces);
    }

    /**
     * Finds and build a DCE. The given uid loads the DCE structure and the
     * fieldList triggers the fillFields which gives the dce its contents
     * and values.
     *
     * @param int $uid
     * @param array $fieldList
     * @param array $contentObject
     * @return \ArminVieweg\Dce\Domain\Model\Dce
     * @throws \UnexpectedValueException
     */
    public function findAndBuildOneByUid($uid, $fieldList, $contentObject)
    {
        $this->disableRespectOfEnableFields();

        if (is_numeric($uid)) {
            /** @var $dce \ArminVieweg\Dce\Domain\Model\Dce */
            $dce = $this->findByUid($uid);
        } else {
            /** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
            $staticDceUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');
            $dce = $staticDceUtility->getStaticDceModel($uid);
        }
        if (get_class($dce) !== 'ArminVieweg\Dce\Domain\Model\Dce') {
            if (is_int($uid)) {
                throw new \UnexpectedValueException('No DCE found with uid "' . $uid . '".', 1328613288);
            }
            throw new \UnexpectedValueException('No static DCE found with identifier "' . $uid . '".', 1328613289);
        }
        $dce = clone $dce;
        $this->cloneFields($dce);

        $this->processFillingFields($dce, $fieldList);
        $dce->setContentObject($contentObject);
        return $dce;
    }

    /**
     * Clones the fields of a dce separately, because cloning the dce just
     * refers the fields
     *
     * @param \ArminVieweg\Dce\Domain\Model\Dce $dce
     * @return void
     */
    protected function cloneFields($dce)
    {
        /** @var $clonedFields \TYPO3\CMS\Extbase\Persistence\ObjectStorage */
        $clonedFields = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
        /** @var $field DceField */
        foreach ($dce->getFields() as $field) {
            $field = clone $field;
            if ($field->getType() === DceField::TYPE_ELEMENT || $field->getType() === DceField::TYPE_SECTION) {
                if ($field->getSectionFields()) {
                    /** @var $clonedFields \TYPO3\CMS\Extbase\Persistence\ObjectStorage */
                    $clonedSectionFields = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
                    foreach ($field->getSectionFields() as $sectionField) {
                        /** @var $clonedSectionField DceField */
                        $clonedSectionField = clone $sectionField;
                        $clonedSectionField->setValue(null);
                        $clonedSectionFields->attach($clonedSectionField);
                        $field->setSectionFields($clonedSectionFields);
                    }
                }
                $clonedFields->attach($field);
                $dce->setFields($clonedFields);
            }
        }
    }

    /**
     * Disable the respect of enable fields in defaultQuerySettings
     *
     * @return void
     */
    protected function disableRespectOfEnableFields()
    {
        /** @var $querySettings Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
        $querySettings->setIgnoreEnableFields(true)->setIncludeDeleted(true);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Walk through the fields and section fields to fill them
     *
     * @param \ArminVieweg\Dce\Domain\Model\Dce $dce
     * @param array $fieldList Field list. Key must contain field variable,
     *                         value its value.
     * @return void
     */
    protected function processFillingFields(\ArminVieweg\Dce\Domain\Model\Dce $dce, array $fieldList)
    {
        foreach ($fieldList as $fieldVariable => $fieldValue) {
            $dceField = $dce->getFieldByVariable($fieldVariable);
            if ($dceField) {
                if (is_array($fieldValue)) {
                    foreach ($fieldValue as $sectionFieldValues) {
                        $sectionFieldValues = current($sectionFieldValues);
                        foreach ($sectionFieldValues as $sectionFieldVariable => $sectionFieldValue) {
                            $sectionField = $dceField->getSectionFieldByVariable($sectionFieldVariable);
                            if ($sectionField instanceof DceField) {
                                $xmlIdent = $dce->getUid() . '-' . $dceField->getVariable() . '-' .
                                    $sectionField->getVariable();
                                $this->fillFields($sectionField, $sectionFieldValue, $xmlIdent, true);
                            }
                        }
                    }
                } else {
                    $xmlIdent = $dce->getUid() . '-' . $dceField->getVariable();
                    $this->fillFields($dceField, $fieldValue, $xmlIdent, false);
                }
            }
        }
    }

    /**
     * Fills the value of given field. If field has special properties some
     * objects or database operations will be do,if not just the given
     * $fieldValue will be add to $dceField->_value. Value of sectionFields
     * will be filled differently.
     *
     * @param DceField $dceField
     * @param string $fieldValue
     * @param string $xmlIdentifier
     * @param bool $isSectionField
     * @return void
     */
    protected function fillFields(
        DceField $dceField,
        $fieldValue,
        $xmlIdentifier,
        $isSectionField = false
    ) {

        $xmlWrapping = 'xml-' . $xmlIdentifier;
        $dceFieldConfiguration = GeneralUtility::xml2array(
            '<' . $xmlWrapping . '>' . $dceField->getConfiguration() . '</' . $xmlWrapping . '>'
        );

        if (is_array($dceFieldConfiguration)) {
            $dceFieldConfiguration = $dceFieldConfiguration['config'];
            if ($dceFieldConfiguration['dce_load_schema'] && $this->hasRelatedObjects($dceFieldConfiguration)) {
                $objects = $this->createObjectsByFieldConfiguration($fieldValue, $dceFieldConfiguration);
            }
            if (isset($objects) && $dceFieldConfiguration['dce_get_first']) {
                $objects = current($objects);
            }
        }
        if ($isSectionField === false) {
            if (isset($objects)) {
                $dceField->setValue($objects);
            } else {
                $dceField->setValue($fieldValue);
            }
        } else {
            $sectionFieldValues = $dceField->getValue();
            if (!is_array($sectionFieldValues)) {
                $sectionFieldValues = array();
            }

            if (isset($objects)) {
                $sectionFieldValues[] = $objects;
            } else {
                $sectionFieldValues[] = $fieldValue;
            }
            $dceField->setValue($sectionFieldValues);
        }
    }

    /**
     * Detects fields
     *
     * @param array $record
     * @return array The record with DCE attributes
     */
    protected function getDceFieldsByRecord(array $record)
    {
        $flexformData = $record['pi_flexform_data'];
        $this->temporaryDceProperties = array();
        if (is_array($flexformData)) {
            $this->getVdefValues($flexformData);
            return $this->temporaryDceProperties;
        }
        return array();
    }

    /**
     * Flatten the given array and extract all vDEF values. Result is stored
     * in $this->dceProperties.
     *
     * @param array $array flexform data array
     * @param Object $caller
     * @param NULL|string $arrayKey
     * @return void
     */
    public function getVdefValues(array $array, $caller = null, $arrayKey = null)
    {
        if ($caller === null) {
            $caller = $this;
        }
        foreach ($array as $key => $value) {
            if ($key === 'vDEF') {
                $caller->temporaryDceProperties[substr($arrayKey, 9)] = $value;
            } elseif (is_array($value) && array_key_exists('el', $value)) {
                $propertyName = substr($key, 9);
                $values = array();
                $i = 1;
                if (is_array(current($value))) {
                    foreach (current($value) as $entry) {
                        if (is_array($entry)) {
                            $entry = $entry['container_' . $propertyName]['el'];
                            if (is_array($entry)) {
                                foreach ($entry as $k => $v) {
                                    $entry[$k] = $v['vDEF'];
                                }
                                $values[$i++] = array('container_' . $propertyName => $entry);
                            }
                        }
                    }
                }
                $caller->temporaryDceProperties[$propertyName] = $values;
            } elseif (is_array($value)) {
                $this->getVdefValues($value, $caller, $key);
            }
        }
    }

    /**
     * Extracts and returns the uid from given DCE CType.
     * Returns FALSE if CType is not a DCE one.
     *
     * @param string $cType
     * @return int|string|bool
     * @static
     */
    public static function extractUidFromCtype($cType)
    {
        if (strpos($cType, 'dceuid') === 0) {
            return intval(substr($cType, 6));
        }
        if (strpos($cType, 'dce_dceuid') === 0) {
            return intval(substr($cType, 10));
        }
        if (strpos($cType, 'dce_') === 0) {
            return substr($cType, 4);
        }
        return false;
    }

    /**
     * Converts a given dce uid to a dce CType.
     *
     * @param int $uid
     * @return string|bool Returns converted CType. If given uid is invalid
     *                     returns FALSE
     * @static
     */
    public static function convertUidToCtype($uid)
    {
        $uid = intval($uid);
        if ($uid === 0) {
            return false;
        }
        return 'dce_dceuid' . $uid;
    }

    /**
     * Checks if given field configuration allows to load sub items
     * (assoc array or objects)
     *
     * @param array $fieldConfiguration
     * @return bool
     */
    protected function hasRelatedObjects(array $fieldConfiguration)
    {
        return in_array($fieldConfiguration['type'], array('group', 'inline', 'select'))
        && (($fieldConfiguration['type'] === 'select' && !empty($fieldConfiguration['foreign_table']))
            || ($fieldConfiguration['type'] === 'inline' && !empty($fieldConfiguration['foreign_table']))
            || ($fieldConfiguration['type'] === 'group' && !empty($fieldConfiguration['allowed'])));
    }

    /**
     * Creates array of assoc array or objects, depending
     * on given field configuration
     *
     * @param string $fieldValue Comma separated list of uids
     * @param array $dceFieldConfiguration
     * @return array
     */
    protected function createObjectsByFieldConfiguration($fieldValue, array $dceFieldConfiguration)
    {
        $objects = array();

        if ($dceFieldConfiguration['type'] === 'group') {
            $className = $dceFieldConfiguration['allowed'];
        } else {
            $className = $dceFieldConfiguration['foreign_table'];
        }
        $tableName = $className;

        $specialClass = null;
        if ($dceFieldConfiguration['dce_load_entity_class']) {
            $className = $dceFieldConfiguration['dce_load_entity_class'];
        } else {
            while (strpos($className, '_') !== false) {
                $position = strpos($className, '_') + 1;
                $className = substr($className, 0, $position - 1) . '-' . strtoupper(substr($className, $position, 1)) .
                    substr($className, $position + 1);
            }

            $className = str_replace('-', '_', $className);
            $className{0} = strtoupper($className{0});
        }

        if ($dceFieldConfiguration['dce_get_fal_objects'] && strtolower($className) === 'sys_file') {
            $className = 'TYPO3\CMS\Core\Resource\File';
        }

        if (strstr($className, '\\') === false) {
            $repositoryName = str_replace('_Model_', '_Repository_', $className) . 'Repository';
        } else {
            $repositoryName = str_replace('\\Model\\', '\\Repository\\', $className) . 'Repository';
        }

        if (strtolower($className) === 'sys_file_collection') {
            $specialClass = 'FileCollection';
            $className = 'TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection';
            $repositoryName = 'TYPO3\CMS\Core\Resource\FileCollectionRepository';
        }

        if (class_exists($className) && class_exists($repositoryName)) {
            // Extbase object found
            $objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
            /** @var $repository \TYPO3\CMS\Extbase\Persistence\Repository */
            $repository = $objectManager->get($repositoryName);

            foreach (GeneralUtility::trimExplode(',', $fieldValue, true) as $uid) {
                $uid = (int)$uid;
                $object = $repository->findByUid($uid);
                if ($specialClass === 'FileCollection') {
                    $object->loadContents();
                }
                $objects[] = $object;
            }
            return $objects;
        }
        // No class found... load DB record and return assoc
        foreach (GeneralUtility::trimExplode(',', $fieldValue, true) as $uid) {
            $uid = (int)$uid;
            $enableFields = '';

            if (!$dceFieldConfiguration['dce_ignore_enablefields']) {
                if (!$GLOBALS['TSFE']->sys_page instanceof \TYPO3\CMS\Frontend\Page\PageRepository) {
                    if (!$GLOBALS['TSFE'] instanceof \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController) {
                        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                            'TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController',
                            $GLOBALS['TYPO3_CONF_VARS'],
                            0,
                            0
                        );
                    }
                    $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
                }
                /** @var $contentObjectRenderer ContentObjectRenderer */
                $contentObjectRenderer = GeneralUtility::makeInstance(
                    'TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer'
                );
                $enableFields = $contentObjectRenderer->enableFields($tableName);
            }

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tableName, 'uid = ' . $uid . $enableFields);
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
                if ($dceFieldConfiguration['dce_enable_autotranslation']) {
                    if ($tableName === 'pages') {
                        $row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row);
                    } else {
                        $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                            $tableName,
                            $row,
                            $GLOBALS['TSFE']->sys_language_uid,
                            $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_overlay']
                        );
                    }
                }

                // Add field with converted flexform_data (as array)
                $row['pi_flexform_data'] = GeneralUtility::xml2array($row['pi_flexform']);

                $dceUid = $this->extractUidFromCtype($row['CType']);
                if ($dceUid !== false) {
                    $objects[] = $this->findAndBuildOneByUid(
                        $dceUid,
                        $this->getDceFieldsByRecord($row),
                        $row
                    );
                } else {
                    $objects[] = $row;
                }
            }
        }
        return $objects;
    }
}
