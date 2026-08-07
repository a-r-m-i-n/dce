<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;

/**
 * Import/Export Hook.
 */
class ImportExportHook
{
    /**
     * Updates the CType of imported DCE content elements after relation remapping.
     */
    public function beforeSetRelation(array $params): void
    {
        $relationPrefix = 'tx_dce_domain_model_dce_';
        foreach (array_keys($params['data']['tt_content'] ?? []) as $ttContentUid) {
            $relation = $params['data']['tt_content'][$ttContentUid]['tx_dce_dce'] ?? null;
            if (!is_string($relation) || !str_starts_with($relation, $relationPrefix)) {
                continue;
            }

            $dceUid = (int)substr($relation, \strlen($relationPrefix));
            $cType = DceRepository::convertUidToCtype($dceUid);
            if (null !== $cType) {
                $params['data']['tt_content'][$ttContentUid]['CType'] = $cType;
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
