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
		$path = static::$extConfiguration['filebasedDcePaths'];
		if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}
		$dceFolderPath = PATH_site . $path . $identifier . DIRECTORY_SEPARATOR;
		if (is_dir($dceFolderPath) && file_exists($dceFolderPath . 'Dce.ts')) {
			$dceConfiguration = file_get_contents($dceFolderPath . 'Dce.ts');
			$configurationArray = static::$typoscriptUtility->parseTypoScriptString($dceConfiguration, TRUE);

			$frontendTemplateFile = $dceFolderPath . 'Frontend.html';
			if (file_exists($frontendTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['template_content'] = file_get_contents($frontendTemplateFile);
			}

			$backendHeaderTemplateFile = $dceFolderPath . 'BackendHeader.html';
			if (file_exists($backendHeaderTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['header_preview'] = file_get_contents($backendHeaderTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'BackendBodytext.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['bodytext_preview'] = file_get_contents($backendBodytextTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'Detailpage.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['detailpage_template'] = file_get_contents($backendBodytextTemplateFile);
			}


			$configurationArray['tx_dce']['static'][$identifier]['template_type'] = 'inline';
			$configurationArray['tx_dce']['static'][$identifier]['preview_template_type'] = 'inline';
			$configurationArray['tx_dce']['static'][$identifier]['detailpage_template_type'] = 'inline';


			return $configurationArray['tx_dce']['static'][$identifier];
		}
	}


	public function fetchRecord($table, $idList, $operation) {
		if ($table === 'tx_dce_domain_model_dce' && strpos($idList, 'dce_') === 0) {
				// DCE record
			static::$staticDceConfiguration = array_merge($this->getStaticDce($idList), array('uid' => $idList, 'type' => 1));
			$this->renderRecord($table, $idList, 0, static::$staticDceConfiguration);
		} else if ($table === 'tx_dce_domain_model_dcefield' && !is_numeric($idList) && static::$staticDceConfiguration !== NULL) {
			if (isset(static::$staticDceConfiguration['fields'][$idList])) {
					// Normal fields
				$row = static::$staticDceConfiguration['fields'][$idList];
				static::$lastField = $idList;
			} else {
					// Section fields (sub)
				if (isset(static::$staticDceConfiguration['fields'][static::$lastField]['section_fields'][$idList])) {
					$row = static::$staticDceConfiguration['fields'][static::$lastField]['section_fields'][$idList];
					if (!isset($row['type'])) {
						$row['type'] = 0;
					}
				}
			}

			$row = array_merge($row, array('uid' => $idList, 'variable' => $idList));
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