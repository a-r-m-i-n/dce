<?php
namespace ArminVieweg\Dce\Slots;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Class TablesDefinitionIsBeingBuiltSlot
 * Signal defined in \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
 *
 * @package ArminVieweg\Dce
 */
class TablesDefinitionIsBeingBuiltSlot
{
    /**
     *
     * @param array $sqlStrings
     * @return array
     */
    public function extendTtContentTable(array $sqlStrings)
    {
        $sqlStrings[] = \ArminVieweg\Dce\Utility\FlexformToTcaMapper::getSql();
        return array($sqlStrings);
    }
}
