<?php
namespace ArminVieweg\Dce\UserFunction;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ItemProfFunc UserFunctions
 *
 * @package ArminVieweg\Dce
 */
class ItemsProcFunc
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
        $parameters['items'][] = [LocalizationUtility::translate('dceTitle', 'dce'), '*dcetitle'];
        if ($parameters['config']['size'] === 1) {
            $parameters['items'][] = [LocalizationUtility::translate('empty', 'dce'), '*empty'];
        }
        if ($parameters['row']['enable_container']) {
            $parameters['items'][] = [LocalizationUtility::translate('containerflag', 'dce'), '*containerflag'];
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
                $parameters['items'][] = [$label, $dceField['variable']];
            }
        }
    }

    /**
     * Add available tt_content columns for TCA mapping to $parameters['items'] array
     *
     * @param array $parameters Referenced parameter array
     * @return void
     */
    public function getAvailableTtContentColumnsForTcaMapping(array &$parameters)
    {
        $excludedColumns = [
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
        ];
        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->admin_get_fields('tt_content');

        $parameters['items'][] = [LocalizationUtility::translate('chooseOption', 'dce'), '--div--'];
        $parameters['items'][] = [LocalizationUtility::translate('noMapping', 'dce'), ''];
        $parameters['items'][] = [LocalizationUtility::translate('mapToIndexColumn', 'dce'), 'tx_dce_index'];
        $parameters['items'][] = [LocalizationUtility::translate('newcol', 'dce'), '*newcol'];
        $parameters['items'][] = [LocalizationUtility::translate('chooseExistingField', 'dce'), '--div--'];
        foreach ($tcaColumns as $fieldName => $column) {
            if (!in_array($fieldName, $excludedColumns) && !empty($dbColumns[$fieldName]['Type'])) {
                $columnInfo = '"' . $dbColumns[$fieldName]['Type'] . '"';
                $parameters['items'][] = [$fieldName . ' - ' . $columnInfo . '', $fieldName];
            }
        }
    }

    /**
     * Add available tt_content columns for palette fields to given $parameters['items'] array
     *
     * @param array $parameters
     * @return void
     */
    public function getAvailableTtContentColumnsForPaletteFields(array &$parameters)
    {
        $excludedColumns = [
            'uid',
            'pid',
            'CType',
            'editlock',
            'pi_flexform',
            'tx_impexp_origuid',
            't3ver_label',
            'tx_dce_dce',
            'tx_dce_index',
            'categories',
            'assets',
            'media'
        ];
        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->admin_get_fields('tt_content');

        $parameters['items'][] = ['--linebreak--', '--linebreak--'];
        foreach ($tcaColumns as $fieldName => $column) {
            if (!in_array($fieldName, $excludedColumns) && !empty($dbColumns[$fieldName]['Type'])) {
                $label = trim($GLOBALS['LANG']->sL($tcaColumns[$fieldName]['label']), ': ');
                if (empty($label)) {
                    $label = $fieldName;
                } else {
                    $label .= ' (' . $fieldName . ')';
                }
                $parameters['items'][] = [$label, $fieldName];
            }
        }
    }
}
