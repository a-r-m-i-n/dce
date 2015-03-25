<?php
namespace ArminVieweg\Dce;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Generates "temp_CACHED_dce_ext_localconf.php" and "temp_CACHED_dce_ext_tables.php" located in /typo3conf/
 * which contains the whole DCE configurations used by TYPO3.
 *
 * @package ArminVieweg\Dce
 */
class Cache {
	/**
	 * @var \ArminVieweg\Dce\Utility\FluidTemplate
	 */
	protected $fluidTemplateUtility;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fluidTemplateUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\FluidTemplate');
	}

	/**
	 * Create localconf
	 *
	 * @param string $pathDceLocalconf
	 *
	 * @return void
	 */
	public function createLocalconf($pathDceLocalconf) {
		$this->fluidTemplateUtility->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/localconf.html');

		/** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
		$staticDceUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');

		$dces = array_merge($this->getDatabaseDce(), $staticDceUtility->getAll());
		$this->fluidTemplateUtility->assign('dceArray', $dces);
		$string = $this->fluidTemplateUtility->render();

		file_put_contents($pathDceLocalconf, $string);
		\TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($pathDceLocalconf);
	}

	/**
	 * Create ext_tables
	 *
	 * @param string $pathDceExtTables
	 *
	 * @return void
	 */
	public function createExtTables($pathDceExtTables) {
		$this->fluidTemplateUtility->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/ext_tables.html');

		/** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
		$staticDceUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');

		$dces = array_merge($this->getDatabaseDce(), $staticDceUtility->getAll(TRUE));
		$this->fluidTemplateUtility->assign('dceArray', $dces);
		$string = $this->fluidTemplateUtility->render();

		file_put_contents($pathDceExtTables, $string);
		\TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($pathDceExtTables);
	}

	/**
	 * Returns all available DCE as array with this format (just most important fields listed):
	 *
	 * DCE
	 * 	|_ uid
	 * 	|_ title
	 * 	|_ tabs <array>
	 * 	|	|_ title
	 * 	|	|_ fields <array>
	 * 	|		|_ uid
	 * 	|		|_ title
	 * 	|		|_ variable
	 * 	|		|_ configuration
	 * 	|_ ...
	 *
	 * @return array with DCE -> containing tabs -> containing fields
	 */
	protected function getDatabaseDce() {

		// fetch the existing DB connection, or initialize it
		/** @var $TYPO3_DB \TYPO3\CMS\Dbal\Database\DatabaseConnection */
		$TYPO3_DB = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();

		$res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'tx_dce_domain_model_dce',
			'deleted=0 AND pid=0',
			'',
			'sorting asc'
		);

		$dce = array();
		while (($row = $TYPO3_DB->sql_fetch_assoc($res))) {
			$res2 = $TYPO3_DB->exec_SELECT_mm_query(
				'*',
				'tx_dce_domain_model_dce',
				'tx_dce_dce_dcefield_mm',
				'tx_dce_domain_model_dcefield',
				' AND tx_dce_domain_model_dce.uid = ' . $row['uid'] . ' AND tx_dce_domain_model_dcefield.deleted = 0 AND tx_dce_domain_model_dcefield.hidden = 0',
				'',
				'tx_dce_dce_dcefield_mm.sorting asc'
			);

			if (TYPO3_MODE === 'FE') {
				$generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('generaltab', 'dce');
			} else {
				$generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab', 'dce');
			}
			$tabs = array(0 => array('title' => $generalTabLabel, 'fields' => array()));
			$i = 0;
			while (($row2 = $TYPO3_DB->sql_fetch_assoc($res2))) {
				if ($row2['type'] === '1') {
					// Create new Tab
					$i++;
					$tabs[$i] = array();
					$tabs[$i]['title'] = $row2['title'];
					$tabs[$i]['fields'] = array();
					continue;
				} elseif ($row2['type'] === '2') {
					$res3 = $TYPO3_DB->exec_SELECTquery(
						'*',
						'tx_dce_domain_model_dcefield as a,tx_dce_dcefield_sectionfields_mm,tx_dce_domain_model_dcefield as b',
						'a.uid=tx_dce_dcefield_sectionfields_mm.uid_local AND b.uid=tx_dce_dcefield_sectionfields_mm.uid_foreign AND a.uid = ' . $row2['uid'] . ' AND b.deleted = 0 AND b.hidden = 0',
						'',
						'tx_dce_dcefield_sectionfields_mm.sorting asc');
					$sectionFields = array();
					while (($row3 = $TYPO3_DB->sql_fetch_assoc($res3))) {
						if ($row3['type'] === '0') {
							// add fields of section to fields
							$sectionFields[] = $row3;
						}
					}
					$row2['section_fields'] = $sectionFields;
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