<?php
namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */

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
    public function __construct(InputInterface $input);

    /**
     * Generating files or configuration and registering it in TYPO3
     */
    public function generate() : void;
}
