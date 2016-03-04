<?php
namespace ArminVieweg\Dce\Components\BackendPreviewTemplates;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * BackendPreviewTemplate utility
 *
 * @package ArminVieweg\Dce
 * @deprecated Remove whole fluid-based backend templating in further versions
 */
class BackendPreviewTemplate
{
    /**
     * If this function has not been disabled in extension settings, it performs an update of all existing content
     * elements, which based on DCE. The preview texts will be updated. This could become delicate if is existing a
     * high amount of such elements.
     *
     * @param int $dceUid
     * @deprecated Remove whole fluid-based backend templating in further versions
     */
    public static function performPreviewAutoupdateBatchOnDceChange($dceUid)
    {
        $uid = (int) $dceUid;
        $dceRow = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'tx_dce_domain_model_dce',
            'uid=' . $uid
        );
        if (isset($dceRow['use_simple_backend_view']) && $dceRow['use_simple_backend_view'] === '1') {
            return;
        }

        $rows = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            'uid',
            'tt_content',
            'CType="dce_dceuid' . $uid . '" AND deleted=0'
        );
        foreach ($rows as $row) {
            $fieldArray = static::generateDcePreview($row['uid']);

            \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
                'tt_content',
                'uid = ' .$row['uid'],
                $fieldArray
            );
        }
    }

    /**
     * Generates the preview texts (header and bodytext) of dce
     *
     * @param int $uid uid of content element
     * @return array
     *
     * @deprecated Remove whole fluid-based backend templating in further versions
     */
    public static function generateDcePreview($uid)
    {
        $settings = array(
            'contentElementUid' => $uid,
            'dceUid' => \ArminVieweg\Dce\Utility\DatabaseUtility::getDceUidByContentElementUid($uid),
        );
        return array(
            'header' => \ArminVieweg\Dce\Utility\Extbase::bootstrapControllerAction(
                'ArminVieweg',
                'Dce',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'header'))
            ),
            'bodytext' => \ArminVieweg\Dce\Utility\Extbase::bootstrapControllerAction(
                'ArminVieweg',
                'Dce',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'bodytext'))
            ),
        );
    }
}
