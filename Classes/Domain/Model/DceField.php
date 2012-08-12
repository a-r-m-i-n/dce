<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 *
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Dce_Domain_Model_DceField extends Tx_Extbase_DomainObject_AbstractEntity {
	/** Field Type: Element */
	const TYPE_ELEMENT = 0;
	/** Field Type: Tab */
	const TYPE_TAB = 1;

	/** @var integer */
	protected $type;

	/** @var string */
	protected $title = '';

	/** @var string */
	protected $variable = '';

	/** @var string */
	protected $configuration = '';

	/** @var string */
	protected $_value = '';


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param integer $type
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
	 */
	public function setValue($value) {
		$this->_value = $value;
	}

}
?>