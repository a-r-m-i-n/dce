<?php
namespace ArminVieweg\Dce\XClass;

/**
 * Class DataPreprocessor
 */
class DataPreprocessor extends \TYPO3\CMS\Backend\Form\DataPreprocessor {
	static protected $extConfiguration = array();

	static protected $staticDceConfiguration = NULL;
	static protected $lastField = NULL;

	/**
	 * @var \Tx_Dce_Utility_TypoScript
	 */
	static protected $typoscriptUtility = NULL;

	/**
	 * Constructor
	 */
	public function __construct() {
		static::$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
		static::$typoscriptUtility = \t3lib_div::makeInstance('Tx_Dce_Utility_TypoScript');
	}


	public function getStaticDce($identifier = '') {
		$paths = \t3lib_div::trimExplode(',', static::$extConfiguration['filebasedDcePaths'], TRUE);
		foreach ($paths as $path) {
			$fullPath = PATH_site . $path . $identifier . DIRECTORY_SEPARATOR;
			if (is_dir($fullPath) && file_exists($fullPath . 'dce.ts')) {
				$dceConfiguration = file_get_contents($fullPath . 'dce.ts');
				$configurationArray = static::$typoscriptUtility->parseTypoScriptString($dceConfiguration, TRUE);

				return $configurationArray['tx_dce']['static'][$identifier];
			}
		}
	}


	public function fetchRecord($table, $idList, $operation) {
		if ($table === 'tx_dce_domain_model_dce' && strpos($idList, 'dce_') === 0) {
			static::$staticDceConfiguration = array_merge($this->getStaticDce($idList), array('uid' => $idList, 'type' => 1));
			$this->renderRecord($table, $idList, 0, static::$staticDceConfiguration);
		} else if ($table === 'tx_dce_domain_model_dcefield' && strpos($idList, 'dce_field_') === 0 && static::$staticDceConfiguration !== NULL) {
			if (isset(static::$staticDceConfiguration['fields'][$idList])) {
					// Normal fields
				$row = static::$staticDceConfiguration['fields'][$idList];
				static::$lastField = $idList;
			} else {
					// Section fields (sub)
				if (isset(static::$staticDceConfiguration['fields'][static::$lastField]['section_fields'][$idList])) {
					$row = static::$staticDceConfiguration['fields'][static::$lastField]['section_fields'][$idList];
				}
			}

			$row = array_merge($row, array('uid' => $idList));
			$this->renderRecord($table, $idList, 0, $row);
		} else {
			parent::fetchRecord($table, $idList, $operation);
		}
	}

	public function renderRecord_inlineProc($data, $fieldConfig, $TSconfig, $table, $row, $field) {
		if (isset($row['uid']) && $table === 'tx_dce_domain_model_dce' && strpos($row['uid'], 'dce_') === 0) {
			if ($field === 'fields') {
				return implode(',', array_keys($row[$field]));
			}
		} else if ($field === 'section_fields' && isset($row['section_fields']) && is_array($row['section_fields'])) {
			return implode(',', array_keys($row['section_fields']));
		}
		return parent::renderRecord_inlineProc($data, $fieldConfig, $TSconfig, $table, $row, $field);
	}
}