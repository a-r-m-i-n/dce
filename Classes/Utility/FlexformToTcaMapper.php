<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Class FlexformToTcaMapper
 * Get SQL for DCE fields which extend tt_content table.
 *
 * @package ArminVieweg\Dce
 */
class FlexformToTcaMapper
{
    /**
     * Returns SQL to add new fields in tt_content
     *
     * @return string SQL CREATE TABLE statement
     */
    public static function getSql()
    {
        $fields = array();
        foreach (static::getDceFieldMappings() as $fieldName => $fieldType) {
            $fields[] = $fieldName . ' ' . $fieldType;
        }
        return 'CREATE TABLE tt_content (' . PHP_EOL . implode(',' . PHP_EOL, $fields) . PHP_EOL . ');';
    }

    /**
     * Returns all DceFields which introduce new columns to tt_content
     *
     * @return array of DceField rows
     */
    public static function getDceFieldRowsWithNewTcaColumns()
    {
        return DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'map_to="*newcol" AND deleted=0 AND type=0 AND new_tca_field_name!="" AND new_tca_field_type!=""'
        );
    }

    /**
     * Returns array with DceFields (in key) and their sql type (value)
     *
     * @return array
     */
    protected static function getDceFieldMappings()
    {
        $fieldMappings = array();
        foreach (static::getDceFieldRowsWithNewTcaColumns() as $dceFieldRow) {
            if ($dceFieldRow['new_tca_field_type'] === 'auto') {
                $fieldMappings[$dceFieldRow['new_tca_field_name']] = static::getAutoFieldType($dceFieldRow);
            } else {
                $fieldMappings[$dceFieldRow['new_tca_field_name']] = $dceFieldRow['new_tca_field_type'];
            }
        }
        return $fieldMappings;
    }

    /**
     * Determines database field type for given DceField, based on the defined field configuration.
     *
     * @param array $dceFieldRow
     * @return string Determined SQL type of given dceFieldRow
     */
    protected static function getAutoFieldType(array $dceFieldRow)
    {
        $fieldConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($dceFieldRow['configuration']);
        switch ($fieldConfiguration['type']) {
            case 'input':
                return 'varchar(255) DEFAULT \'\' NOT NULL';
            case 'check':
            case 'radio':
                return 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
            case 'text':
            case 'select':
            case 'group':
            default:
                return 'text';
        }
    }
}
