<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Import/Export Hook
 *
 * @package ArminVieweg\Dce
 */
class ImportExportHook
{
    /**
     * Update tt_content dce record on import. Also sets global for import in
     * progress indicator used in AfterSaveHook.
     *
     * @param array $params
     * @return void
     */
    public function beforeSetRelation(array $params)
    {
        /** @var array $data */
        $data = $params['data'];

        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain */
        $tceMain = $params['tce'];
        $tceMain->start([], []);

        if (array_key_exists('tt_content', $data)) {
            foreach ($data['tt_content'] as $ttContentUid => $ttContentUpdatedFields) {
                if (array_key_exists('tx_dce_dce', $ttContentUpdatedFields)) {
                    $dceUid = intval(substr($ttContentUpdatedFields['tx_dce_dce'], strlen('tx_dce_domain_model_dce_')));
                    $tceMain->updateDB('tt_content', $ttContentUid, [
                        'CType' => \ArminVieweg\Dce\Domain\Repository\DceRepository::convertUidToCtype($dceUid),
                        'tx_dce_dce' => $dceUid
                    ]);
                }
            }
        }
    }

    /**
     * Sets a global before import of dce starts
     *
     * @param array $params
     * @return void
     */
    public function beforeWriteRecordsRecords(array $params)
    {
        if (array_key_exists('tx_dce_domain_model_dce', $params['data'])) {
            $GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'] = true;
        }
    }
}
