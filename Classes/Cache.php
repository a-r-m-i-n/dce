<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012-2013 Armin Ruediger Vieweg <armin@v.ieweg.de>
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Generates "temp_CACHED_dce_ext_localconf.php" and "temp_CACHED_dce_ext_tables.php" located in /typo3conf/
 * which contains the whole DCE configurations used by TYPO3.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Dce_Cache {
	/**
	 * @var Tx_Dce_Utility_FluidTemplate
	 */
	protected $fluidTemplateUtility;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fluidTemplateUtility = t3lib_div::makeInstance('Tx_Dce_Utility_FluidTemplate');
	}

	/**
	 * Create localconf
	 *
	 * @param string $pathDceLocalconf
	 *
	 * @return void
	 */
    public function createLocalconf($pathDceLocalconf) {
		$this->fluidTemplateUtility->setTemplatePathAndFilename(t3lib_extMgm::extPath('dce') . 'Resources/Private/Templates/DceSource/localconf.html');
		$this->fluidTemplateUtility->assign('dceArray', $this->getDce());
		$string = $this->fluidTemplateUtility->render();

		file_put_contents($pathDceLocalconf, $string);
    }

	/**
	 * Create ext_tables
	 *
	 * @param string $pathDceExtTables
	 *
	 * @return void
	 */
	public function createExtTables($pathDceExtTables) {
		$this->fluidTemplateUtility->setTemplatePathAndFilename(t3lib_extMgm::extPath('dce') . 'Resources/Private/Templates/DceSource/ext_tables.html');
		$this->fluidTemplateUtility->assign('dceArray', $this->getDce());
		$string = $this->fluidTemplateUtility->render();

		file_put_contents($pathDceExtTables, $string);
	}

	/**
	 * Returns all available DCE as array with this format (just most important fields listed):
	 *
	 * DCE
	 * 	|_ uid
	 * 	|_ title
	 *  |_ tabs <array>
	 * 	|	|_ title
	 * 	|	|_ fields <array>
	 *  |    	  |_ uid
	 * 	|		  |_ title
	 * 	|		  |_ variable
	 * 	|		  |_ configuration
	 *	|_ ...
	 *
	 * @return array with DCE -> containing tabs -> containing fields
	 */
	protected function getDce() {
		/** @var $TYPO3_DB t3lib_DB */
		$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
		$TYPO3_DB->connectDB();

		$res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'tx_dce_domain_model_dce',
			'deleted=0 AND pid=0',
			'',
			'sorting asc'
		);

		$dce = array();
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
			$res2 = $TYPO3_DB->exec_SELECT_mm_query(
				'*',
				'tx_dce_domain_model_dce',
				'tx_dce_dce_dcefield_mm',
				'tx_dce_domain_model_dcefield',
				' AND tx_dce_domain_model_dce.uid = ' . $row['uid'] . ' AND tx_dce_domain_model_dcefield.deleted = 0',
				'',
				'tx_dce_dce_dcefield_mm.sorting asc'
			);
			$tabs = array(0 => array('title' => Tx_Extbase_Utility_Localization::translate('generaltab', 'dce'), 'fields' => array()));
			$i = 0;
			while ($row2 = $TYPO3_DB->sql_fetch_assoc($res2)) {
				if ($row2['type'] === '1') {
					// Create new Tab
					$i++;
					$tabs[$i] = array();
					$tabs[$i]['title'] = $row2['title'];
					$tabs[$i]['fields'] = array();
					continue;
				} else if($row2['type'] === '2'){
					$res3 = $TYPO3_DB->exec_SELECTquery(
						'*',
						'tx_dce_domain_model_dcefield as a,tx_dce_dcefield_sectionfields_mm,tx_dce_domain_model_dcefield as b',
						 'a.uid=tx_dce_dcefield_sectionfields_mm.uid_local AND b.uid=tx_dce_dcefield_sectionfields_mm.uid_foreign AND a.uid = ' . $row2['uid'] . ' AND b.deleted = 0',
						'',
						'tx_dce_dcefield_sectionfields_mm.sorting asc');
					$sectionFields = array();
					while ($row3 = $TYPO3_DB->sql_fetch_assoc($res3)) {
						if($row3['type'] === '0'){
							// add fields of section to fields
							$sectionFields[] = $row3;
						}
					}
					$row2['sectionFields'] = $sectionFields;
					$tabs[$i]['fields'][] = $row2;
				} else {
					// usual element
					$tabs[$i]['fields'][] = $row2;
				}
			}
			if (count($tabs[0]['fields']) === 0) {
				unset($tabs[0]);
			}

			$row['tabs'] = $tabs;
			$row['hasCustomWizardIcon'] = ($row['wizard_icon'] === 'custom') ? TRUE : FALSE;
			$dce[] = $row;
		}
		return $dce;
	}
}
?>