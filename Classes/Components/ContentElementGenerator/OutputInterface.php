<?php
namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;

/**
 * OutputInterface
 * for content element generator output classes
 */
interface OutputInterface
{
    /**
     * OutputInterface constructor
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input, PhpFrontend $cacheFrontend);

    /**
     * Generating files or configuration and registering it in TYPO3
     */
    public function generate() : void;
}
