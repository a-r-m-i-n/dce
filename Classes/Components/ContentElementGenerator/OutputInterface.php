<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * OutputInterface
 * for content element generator output classes.
 */
interface OutputInterface
{
    /**
     * OutputInterface constructor.
     */
    public function __construct(InputInterface $input, CacheManager $cacheManagerManager);

    /**
     * Generating files or configuration and registering it in TYPO3.
     */
    public function generate(): void;
}
