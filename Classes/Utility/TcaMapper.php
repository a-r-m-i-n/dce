<?php
namespace ArminVieweg\Dce\Utility;

    /*  | This extension is part of the TYPO3 project. The TYPO3 project is
     *  | free software and is licensed under GNU General Public License.
     *  |
     *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
     */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TcaMapper utility
 *
 * @package ArminVieweg\Dce
 */
class TcaMapper
{

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
        $dceUid = DatabaseUtility::getDceUidByContentElementUid($uid);
        $dceFieldsWithMapping = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $dceUid . ' AND map_to!="" AND deleted=0'
        );
        if (count($dceFieldsWithMapping) === 0 || !isset($piFlexform)) {
            return;
        }

        /** @var array $fieldToTcaMappings */
        $fieldToTcaMappings = array();
        foreach ($dceFieldsWithMapping as $dceField) {
            $mapTo = $dceField['map_to'];
            if ($mapTo === '*newcol') {
                $mapTo = $dceField['new_tca_field_name'];
            }
            $fieldToTcaMappings[$dceField['variable']] = $mapTo;
        }

        $updateData = array();
        $flatFlexFormData = \TYPO3\CMS\Core\Utility\ArrayUtility::flatten(GeneralUtility::xml2array($piFlexform));
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
            $updateStatus = DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
                'tt_content',
                'uid=' . $uid,
                $updateData
            );
            if (!$updateStatus) {
                \ArminVieweg\Dce\Utility\FlashMessage::add(
                    DatabaseUtility::getDatabaseConnection()->sql_error(),
                    'Flexform to TCA mapping failure',
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                );
            }
        }
    }
}
