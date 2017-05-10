<?php
namespace ArminVieweg\Dce\Components\FlexformToTcaMapper;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexformToTcaMapper
 * Get SQL for DCE fields which extend tt_content table.
 *
 * @package ArminVieweg\Dce
 */
class Mapper
{
    /**
     * Returns SQL to add new fields in tt_content
     *
     * @return string SQL CREATE TABLE statement
     */
    public static function getSql()
    {
        $fields = [];
        foreach (static::getDceFieldMappings() as $fieldName => $fieldType) {
            $fields[] = $fieldName . ' ' . $fieldType;
        }
        if (!empty($fields)) {
            return 'CREATE TABLE tt_content (' . PHP_EOL . implode(',' . PHP_EOL, $fields) . PHP_EOL . ');';
        }
        return '';
    }

    /**
     * Returns all DceFields which introduce new columns to tt_content
     *
     * @return array of DceField rows or empty array
     */
    public static function getDceFieldRowsWithNewTcaColumns()
    {
        $rows = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'map_to="*newcol" AND deleted=0 AND type=0 AND new_tca_field_name!="" AND new_tca_field_type!=""'
        );

        if ($rows === null) {
            return [];
        }
        return $rows;
    }

    /**
     * Returns array with DceFields (in key) and their sql type (value)
     *
     * @return array
     */
    protected static function getDceFieldMappings()
    {
        $fieldMappings = [];
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
        $fieldConfiguration = GeneralUtility::xml2array($dceFieldRow['configuration']);
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

    /**
     * Check if DceFields has been mapped with TCA columns
     * and writes values to columns in database, if so.
     *
     * @param int $uid
     * @param string $piFlexform
     * @return void
     */
    public static function saveFlexformValuesToTca($uid, $piFlexform)
    {
        $dceUid = \ArminVieweg\Dce\Utility\DatabaseUtility::getDceUidByContentElementUid($uid);
        $dceFieldsWithMapping = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $dceUid . ' AND map_to!="" AND deleted=0'
        );
        if (count($dceFieldsWithMapping) === 0 || !isset($piFlexform) || empty($piFlexform)) {
            return;
        }

        $flexFormArray = GeneralUtility::xml2array($piFlexform);
        if (!is_array($flexFormArray)) {
            return;
        }

        /** @var array $fieldToTcaMappings */
        $fieldToTcaMappings = [];
        foreach ($dceFieldsWithMapping as $dceField) {
            $mapTo = $dceField['map_to'];
            if ($mapTo === '*newcol') {
                $mapTo = $dceField['new_tca_field_name'];
            }
            $fieldToTcaMappings[$dceField['variable']] = $mapTo;
        }

        $updateData = [];
        $flatFlexFormData = \TYPO3\CMS\Core\Utility\ArrayUtility::flatten($flexFormArray);
        foreach ($flatFlexFormData as $key => $value) {
            $fieldName = preg_replace('/.*settings\.(.*?)\.vDEF$/', '$1', $key);
            if (array_key_exists($fieldName, $fieldToTcaMappings)) {
                if (empty($updateData[$fieldToTcaMappings[$fieldName]])) {
                    $updateData[$fieldToTcaMappings[$fieldName]] = $value;
                } else {
                    $updateData[$fieldToTcaMappings[$fieldName]] .= PHP_EOL . PHP_EOL . $value;
                }
            }
        }

        if (!empty($updateData)) {
            $updateStatus = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
                'tt_content',
                'uid=' . $uid,
                $updateData
            );
            if (!$updateStatus) {
                \ArminVieweg\Dce\Utility\FlashMessage::add(
                    \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->sql_error(),
                    'Flexform to TCA mapping failure',
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                );
            }
        }
    }
}
