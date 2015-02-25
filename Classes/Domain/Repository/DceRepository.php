<?php
namespace DceTeam\Dce\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 * DCE repository
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DceRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 *
	 * @param bool $includeHidden
	 * @return array
	 */
	public function findAllAndStatics($includeHidden = FALSE) {
		/** @var \DceTeam\Dce\Utility\StaticDce $staticDceUtility */
		$staticDceUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DceTeam\Dce\Utility\StaticDce');
		$staticDces = $staticDceUtility->getAll();

		if ($includeHidden) {
			$this->defaultQuerySettings->setIgnoreEnableFields(TRUE);
		}
		$databaseDces = $this->findAll()->toArray();
		return array_merge($databaseDces, $staticDces);
	}

	/**
	 * Finds and build a DCE. The given uid loads the DCE structure and the fieldList triggers the fillFields which
	 * gives the dce its contents and values.
	 *
	 * @param int $uid
	 * @param array $fieldList
	 * @param array $contentObject
	 * @return \DceTeam\Dce\Domain\Model\Dce
	 * @throws UnexpectedValueException
	 */
	public function findAndBuildOneByUid($uid, $fieldList, $contentObject) {
		$this->disableRespectOfEnableFields();

		if (is_numeric($uid)) {
			/** @var $dce \DceTeam\Dce\Domain\Model\Dce */
			$dce = $this->findByUid($uid);
		} else {
			/** @var \DceTeam\Dce\Utility\StaticDce $staticDceUtility */
			$staticDceUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DceTeam\Dce\Utility\StaticDce');
			$dce = $staticDceUtility->getStaticDceModel($uid);
		}
		if (get_class($dce) !== 'DceTeam\Dce\Domain\Model\Dce') {
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
	 * Clones the fields of a dce separately, because cloning the dce just refers the fields
	 *
	 * @param \DceTeam\Dce\Domain\Model\Dce $dce
	 * @return void
	 */
	protected function cloneFields($dce) {
		/** @var $clonedFields \TYPO3\CMS\Extbase\Persistence\ObjectStorage */
		$clonedFields = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		/** @var $field \DceTeam\Dce\Domain\Model\DceField */
		foreach($dce->getFields() as $field) {
			$field = clone $field;
			if ($field->getType() === \DceTeam\Dce\Domain\Model\DceField::TYPE_ELEMENT || $field->getType() ===  \DceTeam\Dce\Domain\Model\DceField::TYPE_SECTION) {
				if ($field->getSectionFields()) {
					/** @var $clonedFields \TYPO3\CMS\Extbase\Persistence\ObjectStorage */
					$clonedSectionFields = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Extbase\Persistence\ObjectStorage');
					foreach($field->getSectionFields() as $sectionField) {
						/** @var $clonedSectionField \DceTeam\Dce\Domain\Model\DceField */
						$clonedSectionField = clone $sectionField;
						$clonedSectionField->setValue(NULL);
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
	protected function disableRespectOfEnableFields() {
		/** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
		$querySettings = $this->objectManager->create('\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 6002000) {
			$querySettings->setIgnoreEnableFields(TRUE)->setIncludeDeleted(TRUE);
		} else {
			$querySettings->setRespectEnableFields(FALSE);
		}
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Walk through the fields and section fields to fill them
	 *
	 * @param \DceTeam\Dce\Domain\Model\Dce $dce
	 * @param array $fieldList Field list. Key must contain field variable, value its value.
	 * @return void
	 */
	protected function processFillingFields(\DceTeam\Dce\Domain\Model\Dce $dce, array $fieldList) {
		foreach ($fieldList as $fieldVariable => $fieldValue) {
			$dceField = $dce->getFieldByVariable($fieldVariable);
			if ($dceField) {
				if (is_array($fieldValue)) {
					foreach($fieldValue as $sectionFieldValues) {
						$sectionFieldValues = current($sectionFieldValues);
						foreach($sectionFieldValues as $sectionFieldVariable => $sectionFieldValue) {
							$sectionField = $dceField->getSectionFieldByVariable($sectionFieldVariable);
							if ($sectionField instanceof \DceTeam\Dce\Domain\Model\DceField) {
								$xmlIdentifier = $dce->getUid() . '-' . $dceField->getVariable() . '-' . $sectionField->getVariable();
								$this->fillFields($sectionField, $sectionFieldValue, TRUE, $xmlIdentifier);
							}
						}
					}
				} else {
					$xmlIdentifier = $dce->getUid() . '-' . $dceField->getVariable();
					$this->fillFields($dceField, $fieldValue, FALSE, $xmlIdentifier);
				}
			}
		}
	}

	/**
	 * Fills the value of given field. If field has special properties some objects or database operations will be do,
	 * if not just the given $fieldValue will be add to $dceField->_value. Value of sectionFields will be filled
	 * differently.
	 *
	 * @param \DceTeam\Dce\Domain\Model\DceField $dceField
	 * @param string $fieldValue
	 * @param bool $isSectionField
	 * @param string $xmlIdentifier
	 * @return void
	 */
	protected function fillFields(\DceTeam\Dce\Domain\Model\DceField $dceField, $fieldValue, $isSectionField = FALSE, $xmlIdentifier) {
		$xmlWrapping = 'xml-' . $xmlIdentifier;
		$dceFieldConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array('<' . $xmlWrapping . '>' . $dceField->getConfiguration() . '</' . $xmlWrapping . '>');

		if (is_array($dceFieldConfiguration)) {
			$dceFieldConfiguration = $dceFieldConfiguration['config'];
			if ($dceFieldConfiguration['dce_load_schema'] && $this->hasRelatedObjects($dceFieldConfiguration)) {
				$objects = $this->createObjectsByFieldConfiguration($fieldValue, $dceFieldConfiguration);
			}
			if (isset($objects) && $dceFieldConfiguration['dce_get_first']) {
				$objects = current($objects);
			}
		}
		if ($isSectionField === FALSE) {
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
	protected function getDceFieldsByRecord(array $record) {
		$flexformData = $record['pi_flexform_data'];
		$this->temporaryDceProperties = array();
		if (is_array($flexformData)) {
			$this->getVDefValues($flexformData);
			return $this->temporaryDceProperties;
		}
		return array();
	}

	/**
	 * Flatten the given array and extract all vDEF values. Result is stored in $this->dceProperties.
	 *
	 * @param array $array flexform data array
	 * @param Object $caller
	 * @param null|string $arrayKey
	 * @return void
	 */
	public function getVDefValues(array $array, $caller = NULL, $arrayKey = NULL) {
		if ($caller === NULL) {
			$caller = $this;
		}
		foreach($array as $key => $value) {
			if ($key === 'vDEF') {
				$caller->temporaryDceProperties[substr($arrayKey, 9)] = $value;
			}
			elseif (is_array($value) && array_key_exists('el', $value)) {
				$propertyName = substr($key, 9);
				$values = array();
				$i = 1;
				if (is_array(current($value))) {
					foreach (current($value) as $entry) {
						if (is_array($entry)) {
							$entry = $entry['container_' . $propertyName]['el'];
							if (is_array($entry)) {
								foreach($entry as $k => $v) {
									$entry[$k] = $v['vDEF'];
								}
								$values[$i++] = array('container_' . $propertyName => $entry);
							}
						}
					}
				}
				$caller->temporaryDceProperties[$propertyName] = $values;
			} elseif (is_array($value)) {
				$this->getVDefValues($value, $caller, $key);
			}
		}
	}

	/**
	 * Extracts and returns the uid from given DCE CType. Returns FALSE if CType is not a DCE one.
	 *
	 * @param string $CType
	 * @return int|string|bool
	 * @static
	 */
	static public function extractUidFromCType($CType) {
		if (strpos($CType, 'dceuid') === 0) {
			return intval(substr($CType, 6));
		}
		if (strpos($CType, 'dce_dceuid') === 0) {
			return intval(substr($CType, 10));
		}
		if (strpos($CType, 'dce_') === 0) {
			return substr($CType, 4);
		}
		return FALSE;
	}

	/**
	 * Converts a given dce uid to a dce CType.
	 *
	 * @param int $uid
	 * @return string|bool Returns converted CType. If given uid is invalid, returns FALSE
	 * @static
	 */
	static public function convertUidToCType($uid) {
		$uid = intval($uid);
		if ($uid === 0) {
			return FALSE;
		}
		return 'dce_dceuid' . $uid;
	}

	/**
	 * Checks if given field configuration allows to load sub items (assoc array or objects)
	 *
	 * @param array $fieldConfiguration
	 * @return bool
	 */
	protected function hasRelatedObjects(array $fieldConfiguration) {
		return  in_array($fieldConfiguration['type'], array('group', 'inline', 'select'))
			&& (($fieldConfiguration['type'] === 'select' && !empty($fieldConfiguration['foreign_table'])) || ($fieldConfiguration['type'] === 'group' && !empty($fieldConfiguration['allowed'])));
	}

	/**
	 * Creates array of assoc array or objects, depending on given field configuration
	 *
	 * @param string $fieldValue Comma separated list of uids
	 * @param array $dceFieldConfiguration
	 * @return array
	 */
	protected function createObjectsByFieldConfiguration($fieldValue, array $dceFieldConfiguration)	{
		$objects = array();

		if ($dceFieldConfiguration['type'] === 'group') {
			$classname = $dceFieldConfiguration['allowed'];
		} else {
			$classname = $dceFieldConfiguration['foreign_table'];
		}
		$tablename = $classname;

		while (strpos($classname, '_') !== FALSE) {
			$position = strpos($classname, '_') + 1;
			$classname = substr($classname, 0, $position - 1) . '-' . strtoupper(substr($classname, $position, 1)) . substr($classname, $position + 1);
		}

		$classname = str_replace('-', '_', $classname);
		$classname{0} = strtoupper($classname{0});
        $specialClass = NULL;

		if ($dceFieldConfiguration['dce_get_fal_objects'] && strtolower($classname) === 'sys_file' && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 6001000) {
			$classname = 'TYPO3\\CMS\\Core\\Resource\\File';
		}

        $repositoryName = str_replace('_Model_', '_Repository_', $classname) . 'Repository'; // !

        if (strtolower($classname) === 'sys_file_collection' && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 6000000) {
            $specialClass = 'FileCollection';
            $classname = 'TYPO3\\CMS\\Core\\Resource\\Collection\\AbstractFileCollection';
            $repositoryName = 'TYPO3\\CMS\\Core\\Resource\\FileCollectionRepository';
        }

		if (class_exists($classname) && class_exists($repositoryName)) {
				// Extbase object found
			$objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
			/** @var $repository \TYPO3\CMS\Extbase\Persistence\Repository */
			$repository = $objectManager->get($repositoryName);

			foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $fieldValue, TRUE) as $uid) {
                $object = $repository->findByUid($uid);
                if ($specialClass === 'FileCollection') {
                    $object->loadContents();
                }
				$objects[] = $object;
			}
			return $objects;
		} else {
				// No class found... load DB record and return assoc
			foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $fieldValue, TRUE) as $uid) {
				$enableFields = '';

				if (!$dceFieldConfiguration['dce_ignore_enablefields']) {
					if (!$GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect) {
						$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility ::makeInstance('t3lib_pageSelect');
					}
					/** @var $cObj tslib_cObj */
					$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
					$enableFields = $cObj->enableFields($tablename);
				}

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tablename, 'uid = ' . $uid . $enableFields);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					if ($dceFieldConfiguration['dce_enable_autotranslation']) {
						if ($tablename === 'pages') {
							$row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row);
						} else {
							$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($tablename, $row, $GLOBALS['TSFE']->sys_language_uid, $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_overlay']);
						}
					}

						// Add field with converted flexform_data (as array)
					$row['pi_flexform_data'] = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['pi_flexform']);

					$dceUid = $this->extractUidFromCType($row['CType']);
					if ($dceUid !== FALSE) {
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
}