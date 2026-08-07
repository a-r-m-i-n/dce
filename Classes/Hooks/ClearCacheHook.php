<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\ContentElementGenerator\Generator;
use T3\Dce\Components\CustomIconProvider;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Flushes DCE code cache files.
 *
 * @see $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['dce']
 */
class ClearCacheHook
{
    public function __construct(private readonly CustomIconProvider $customIconProvider)
    {
    }

    public function flushDceCache(array $parameters, DataHandler $dataHandler): void
    {
        if ('tx_dce_domain_model_dce' === ($parameters['table'] ?? null)
            || 'all' === ($parameters['cacheCmd'] ?? null)
        ) {
            $this->customIconProvider->flushCache();
        }

        if (isset($parameters['cacheCmd']) && 'all' === $parameters['cacheCmd']) {
            (new Generator())->rebuild();
        }
    }
}
