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
class Tx_Dce_Controller_DceController extends Tx_Extbase_MVC_Controller_ActionController {

    /**
     * dceRepository
     *
     * @var Tx_Dce_Domain_Repository_DceRepository
     */
    protected $dceRepository;

	/**
	 * @var Tx_Dce_Domain_Repository_DceFieldRepository
	 */
	protected $dceFieldRepository;

    /**
     * injectDceRepository
     *
     * @param Tx_Dce_Domain_Repository_DceRepository $dceRepository
     * @return void
     */
    public function injectDceRepository(Tx_Dce_Domain_Repository_DceRepository $dceRepository) {
        $this->dceRepository = $dceRepository;
    }

	/**
	 * Injects the dceFieldRepository
	 *
	 * @param Tx_Dce_Domain_Repository_DceFieldRepository $repository
	 *
	 * @return void
	 */
	public function injectDceFieldRepository(Tx_Dce_Domain_Repository_DceFieldRepository $repository) {
		$this->dceFieldRepository = $repository;
	}

    /**
     * action show
     *
     * @return string output of content element's settings
	 *
	 * @TODO refactor it!
     */
    public function showAction() {
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		/** @var $dce Tx_Dce_Domain_Model_Dce */
		$dce = $this->dceRepository->findByUid(intval(substr($config['pluginName'], 6)));
		if (get_class($dce) !== 'Tx_Dce_Domain_Model_Dce') {
			throw new UnexpectedValueException('No DCE found with CType "' . $config['pluginName'] . '".', 1328613288);
		}

		/** @var $fluidTemplate Tx_Fluid_View_StandaloneView */
		$fluidTemplate = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		if ($dce->getTemplateType() === 'inline') {
			$fluidTemplate->setTemplateSource($dce->getTemplateContent());
		} else {
			$fluidTemplate->setTemplatePathAndFilename($dce->getTemplateFile());
		}

		$fluidTemplate->setLayoutRootPath(t3lib_div::getFileAbsFileName($dce->getTemplateLayoutRootPath()));
		$fluidTemplate->setPartialRootPath(t3lib_div::getFileAbsFileName($dce->getTemplatePartialRootPath()));


		$fields = array();
		// walk through the fields and validate/fill them
		foreach($this->settings as $fieldVariable => $fieldValue) {
			$dceField = $this->dceFieldRepository->findOneByDceAndVariable($dce, $fieldVariable);
			if ($dceField) {
				$dceFieldConfiguration = t3lib_div::xml2array($dceField->getConfiguration());

				if (in_array($dceFieldConfiguration['type'], array('group', 'inline'))
						&&
						(
						($dceFieldConfiguration['type'] === 'select' && !empty($dceFieldConfiguration['foreign_table']))
						 || ($dceFieldConfiguration['type'] === 'group' && !empty($dceFieldConfiguration['allowed']))
						)
						&& $dceFieldConfiguration['dce_load_schema']
				) {

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
					$repositoryName = str_replace('_Model_', '_Repository_', $classname) . 'Repository'; // !

					if (class_exists($classname) && class_exists($repositoryName)) {
						// Extbase object found
						$objectManager = new Tx_Extbase_Object_ObjectManager();
						/** @var $repository Tx_Extbase_Persistence_Repository */
						$repository = $objectManager->get($repositoryName);

						$objects = array();
						foreach(t3lib_div::trimExplode(',', $fieldValue, TRUE) as $uid) {
							$objects[] = $repository->findByUid($uid);
						}
					} else {
						// No class found... load DB record and return assoc
						$objects = array();
						foreach(t3lib_div::trimExplode(',', $fieldValue, TRUE) as $uid) {
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tablename, 'uid = ' . $uid);
							while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
								$objects[] = $row;
							}
						}
					}
				}

				if (isset($objects)) {
					$fields[$fieldVariable] = $objects;
					unset($objects);
				} else {
					$fields[$fieldVariable] = $fieldValue;
				}
			}
		}


		$fluidTemplate->assign('dce', $dce);
		$fluidTemplate->assign('field', $fields);
		$fluidTemplate->assign('fields', $fields);

		$output = $fluidTemplate->render();
		return $output;
    }

}
?>