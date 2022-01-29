<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Import/Export Hook.
 */
class ImportExportHook
{
    /**
     * Update tt_content dce record on import. Also sets global for import in
     * progress indicator used in AfterSaveHook.
     */
    public function beforeSetRelation(array $params): void
    {
        /** @var array $data */
        $data = $params['data'];

        /** @var DataHandler $tceMain */
        $tceMain = $params['tce'];
        $tceMain->start([], []);

        if (array_key_exists('tt_content', $data)) {
            foreach ($data['tt_content'] as $ttContentUid => $ttContentUpdatedFields) {
                if (array_key_exists('tx_dce_dce', $ttContentUpdatedFields)) {
                    $dceUid = (int)substr($ttContentUpdatedFields['tx_dce_dce'], \strlen('tx_dce_domain_model_dce_'));
                    $tceMain->updateDB('tt_content', $ttContentUid, [
                        'CType' => DceRepository::convertUidToCtype($dceUid),
                        'tx_dce_dce' => $dceUid,
                    ]);
                }
            }
        }
    }

    /**
     * Sets a global before import of dce starts.
     */
    public function beforeWriteRecordsRecords(array $params): void
    {
        if (array_key_exists('tx_dce_domain_model_dce', $params['data'])) {
            $GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'] = true;
        }
    }
}
