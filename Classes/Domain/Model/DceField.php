<?php

namespace DceTeam\Dce\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 * Model for DCE fields. Contains configuration of fields and fetched values.
 * These fields are part of the DCE model.
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */


class DceField extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
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

	/** @var string */
	protected $_value = '';

	/** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DceField> */
	protected $sectionFields = NULL;


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->sectionFields = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @return int
	 */
	public function getType() {
		return (int) $this->type;
	}

	/**
	 * @param int $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
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
	public function getVariable() {
		return $this->variable;
	}

	/**
	 * @param string $variable
	 * @return void
	 */
	public function setVariable($variable) {
		$this->variable = $variable;
	}

	/**
	 * @return string
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param string $configuration
	 * @return void
	 */
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setValue($value) {
		$this->_value = $value;
	}

	/**
	 * Checks if section field count is greater than zero
	 *
	 * @return bool Returns TRUE when section fields existing, otherwise returns FALSE
	 */
	public function hasSectionFields() {
		$sectionFields = $this->getSectionFields();
		return isset($sectionFields) && $sectionFields->count() > 0;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getSectionFields() {
		return $this->sectionFields;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields
	 * @return void
	 */
	public function setSectionFields(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sectionFields) {
		$this->sectionFields = $sectionFields;
	}

	/**
	 * Adds a section field
	 *
	 * @param DceField $sectionField
	 * @return void
	 */
	public function addSectionField(DceField $sectionField) {
		$this->sectionFields->attach($sectionField);
	}

	/**
	 * Removes a section field
	 *
	 * @param DceField $sectionField
	 * @return void
	 */
	public function removeSectionField(DceField $sectionField) {
		$this->sectionFields->detach($sectionField);
	}

	/**
	 * Checks attached sectionFields for given variable and returns the single field if found. If not found, returns NULL.
	 *
	 * @param string $variable
	 * @return null|DceField
	 */
	public function getSectionFieldByVariable($variable) {
		/** @var $sectionField DceField */
		foreach($this->getSectionFields() as $sectionField) {
			if($sectionField->getVariable() === $variable) {
				return $sectionField;
			}
		}
		return NULL;
	}

	/**
	 * Checks if the field is of type element
	 *
	 * @return bool
	 */
	public function isElement() {
		return ($this->getType() === self::TYPE_ELEMENT);
	}


	/**
	 * Checks if the field is of type section
	 *
	 * @return bool
	 */
	public function isSection() {
		return ($this->getType() === self::TYPE_SECTION);
	}


	/**
	 * Checks if the field is of type tab
	 *
	 * @return bool
	 */
	public function isTab() {
		return ($this->getType() === self::TYPE_TAB);
	}
}