<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * InputInterface
 * for content element generator input classes.
 */
interface InputInterface
{
    /**
     * Returns all available DCE as array with this format
     * (just most important fields listed):.
     *
     * DCE
     *    |_ uid
     *    |_ title
     *    |_ tabs <array>
     *    |    |_ title
     *    |    |_ fields <array>
     *    |        |_ uid
     *    |        |_ title
     *    |        |_ variable
     *    |        |_ configuration
     *    |_ ...
     *
     * @return array with DCE -> containing tabs -> containing fields
     */
    public function getDces(): array;
}
