<?php

namespace T3\Dce\UserFunction;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\LanguageService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ItemProfFunc UserFunctions.
 */
class ItemsProcFunc
{
    /**
     * Add DceFields.
     *
     * @param array $parameters Referenced parameter array
     */
    public function getDceFields(array &$parameters): void
    {
        if (!isset($parameters['row']['uid']) || !is_numeric($parameters['row']['uid'])) {
            return;
        }
        $parameters['items'][] = [LocalizationUtility::translate('dceTitle', 'dce'), '*dcetitle'];
        if (1 === $parameters['config']['size']) {
            $parameters['items'][] = [LocalizationUtility::translate('empty', 'dce'), '*empty'];
        }
        if ($parameters['row']['enable_container']) {
            $parameters['items'][] = [LocalizationUtility::translate('containerflag', 'dce'), '*containerflag'];
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFields = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_dce',
                    $queryBuilder->createNamedParameter($parameters['row']['uid'], \PDo::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'type',
                    $queryBuilder->createNamedParameter([0, 2], Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();

        if (!empty($dceFields)) {
            foreach ($dceFields as $dceField) {
                $label = LanguageService::sL($dceField['title']);
                if ('2' === $dceField['type']) {
                    $label .= ' (' . LocalizationUtility::translate('section', 'dce') . ')';
                }
                $parameters['items'][] = [$label, $dceField['variable']];
            }
        }
    }

    /**
     * Add available tt_content columns for TCA mapping.
     *
     * @param array $parameters Referenced parameter array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAvailableTtContentColumnsForTcaMapping(array &$parameters): void
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
            'tx_dce_index',
            'tx_dce_new_container',
            'tx_dce_slug',
        ];
        // Do not show column which has been provided by itself
        if ('tx_dce_domain_model_dcefield' === $parameters['table'] &&
            '*newcol' === $parameters['row']['map_to'] &&
            !empty($parameters['row']['new_tca_field_name'])
        ) {
            $excludedColumns[] = $parameters['row']['new_tca_field_name'];
        }
        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = DatabaseUtility::adminGetFields('tt_content');

        $parameters['items'][] = [LocalizationUtility::translate('chooseOption', 'dce'), '--div--'];
        $parameters['items'][] = [LocalizationUtility::translate('noMapping', 'dce'), ''];
        $parameters['items'][] = [LocalizationUtility::translate('mapToIndexColumn', 'dce'), 'tx_dce_index'];
        $parameters['items'][] = [LocalizationUtility::translate('newcol', 'dce'), '*newcol'];
        $parameters['items'][] = [LocalizationUtility::translate('chooseExistingField', 'dce'), '--div--'];
        foreach (array_keys($tcaColumns) as $fieldName) {
            if (!empty($dbColumns[$fieldName]['Type']) && !\in_array($fieldName, $excludedColumns, true)) {
                $columnInfo = '"' . trim($dbColumns[$fieldName]['Type'], ' \\') . '"';
                $parameters['items'][] = [$fieldName . ' - ' . $columnInfo . '', $fieldName];
            }
        }
    }

    /**
     * Add available tt_content columns for palette fields.
     */
    public function getAvailableTtContentColumnsForPaletteFields(array &$parameters): void
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
            'media',
            'tx_dce_new_container',
            'tx_dce_slug',
        ];
        // Do not offer fields used for TCA mapping. They are by default configured as passthrough.
        $mappedColumns = array_keys(Mapper::getDceFieldMappings());
        if (!empty($mappedColumns)) {
            $excludedColumns = array_merge($excludedColumns, $mappedColumns);
        }

        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = DatabaseUtility::adminGetFields('tt_content');

        $parameters['items'][] = ['--linebreak--', '--linebreak--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak1--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak2--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak3--'];
        foreach (array_keys($tcaColumns) as $fieldName) {
            if (!empty($dbColumns[$fieldName]['Type']) && !\in_array($fieldName, $excludedColumns, true)) {
                $label = '';
                if (isset($tcaColumns[$fieldName]['label'])) {
                    $label = trim($GLOBALS['LANG']->sL($tcaColumns[$fieldName]['label']), ': ');
                }
                if (empty($label)) {
                    $label = $fieldName;
                } else {
                    $label .= ' (' . $fieldName . ')';
                }
                $parameters['items'][] = [$label, $fieldName];
            }
        }
    }

    /**
     * Adds available wizard icons.
     */
    public function getAvailableWizardIcons(array &$parameters): void
    {
        $identifiers = [
            'content-header',
            'content-textpic',
            'content-bullets',
            'content-table',
            'content-special-uploads',
            'content-special-menu',
            'content-special-html',
            'content-special-div',
            'content-special-shortcut',
            'content-elements-login',
            'content-elements-mailform',
            'content-plugin',
        ];

        foreach ($identifiers as $identifier) {
            $parameters['items'][] = [
                'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:wizardIcon.' . $identifier,
                $identifier,
                $identifier,
            ];
        }
        $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:';
        $parameters['items'][] = [$ll . 'wizardIcon.custom', '--div--'];
        $parameters['items'][] = [$ll . 'wizardIcon.customIcon', 'custom'];
    }
}
