<?php
namespace ArminVieweg\Dce\UserFunction;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ItemProfFunc UserFunctions
 *
 * @package ArminVieweg\Dce
 */
class ItemProcFunc
{
    /**
     * Add DceFields to referenced $parameters['items'] array
     *
     * @param array $parameters Referenced parameter array
     * @return void
     */
    public function getDceFields(array &$parameters)
    {
        if (!isset($parameters['row']['uid']) || !is_numeric($parameters['row']['uid'])) {
            return;
        }
        $parameters['items'][] = array(LocalizationUtility::translate('dceTitle', 'dce'), '*dcetitle');
        if ($parameters['config']['size'] === 1) {
            $parameters['items'][] = array(LocalizationUtility::translate('empty', 'dce'), '*empty');
        }
        if ($parameters['row']['enable_container']) {
            $parameters['items'][] = array(LocalizationUtility::translate('containerflag', 'dce'), '*containerflag');
        }

        $database = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        $dceFields = $database->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $parameters['row']['uid'] . ' AND deleted=0 AND type IN (0,2)',
            '',
            'sorting asc'
        );
        if (!empty($dceFields)) {
            foreach ($dceFields as $dceField) {
                $label = LanguageService::sL($dceField['title']);
                if ($dceField['type'] === '2') {
                    $label .= ' (' . LocalizationUtility::translate('section', 'dce') . ')';
                }
                $parameters['items'][] = array($label, $dceField['variable']);
            }
        }
    }

    /**
     * Add available tt_content columns to $parameters['items'] array
     *
     * @param array $parameters Referenced parameter array
     * @return void
     */
    public function getAvailableTtContentColumns(array &$parameters)
    {
        $excludedColumns = array(
            'uid',
            'pid',
            'CType',
            'editlock',
            'sys_language_uid',
            'l18n_parent',
            'colPos',
            'pi_flexform',
            'tx_impexp_origuid',
            'l18n_diffsource',
            't3ver_label',
            'tx_dce_dce',
            'tx_dce_index'
        );
        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->admin_get_fields('tt_content');

        $parameters['items'][] = array(LocalizationUtility::translate('chooseOption', 'dce'), '--div--');
        $parameters['items'][] = array(LocalizationUtility::translate('noMapping', 'dce'), '');
        $parameters['items'][] = array(LocalizationUtility::translate('mapToIndexColumn', 'dce'), 'tx_dce_index');
        $parameters['items'][] = array(LocalizationUtility::translate('newcol', 'dce'), '*newcol');
        $parameters['items'][] = array(LocalizationUtility::translate('chooseExistingField', 'dce'), '--div--');
        foreach ($tcaColumns as $name => $column) {
            if (!in_array($name, $excludedColumns) && !empty($dbColumns[$name]['Type'])) {
                $columnInfo = '"' . $dbColumns[$name]['Type'] . '"';
                $parameters['items'][] = array($name . ' - ' . $columnInfo . '', $name);
            }
        }
    }
}
