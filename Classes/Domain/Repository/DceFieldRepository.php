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
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Dce_Domain_Repository_DceFieldRepository extends Tx_Extbase_Persistence_Repository {
	/**
	 * @param $dce
	 * @param $variable
	 *
	 * @return Tx_Dce_Domain_Model_DceField
	 */
	public function findOneByDceAndVariable($dce, $variable) {
		$query = $this->createQuery();
		$query->matching($query->logicalAnd(array(
			$query->equals('variable', $variable),
			$query->equals('type', Tx_Dce_Domain_Model_DceField::TYPE_ELEMENT)
		)));

		$result = $query->execute();

		if ($result->count() > 1) {
			/** @var $field Tx_Dce_Domain_Model_DceField */
			foreach ($result as $field) {
				/** @var $dceField Tx_Dce_Domain_Model_DceField */
				foreach($dce->getFields() as $dceField) {
					if ($field->getUid() === $dceField->getUid()) {
						return $field;
					}
				}
			}
		}
		return $result->getFirst();
	}
}
?>