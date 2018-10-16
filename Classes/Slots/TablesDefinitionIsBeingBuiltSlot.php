<?php
namespace ArminVieweg\Dce\Slots;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;

/**
 * Class TablesDefinitionIsBeingBuiltSlot
 * Signal defined in \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
 */
class TablesDefinitionIsBeingBuiltSlot
{
    /**
     *
     * @param array $sqlStrings
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function extendTtContentTable(array $sqlStrings)
    {
        if ($this->checkRequiredFieldsExisting()) {
            $sqlStrings[] = \ArminVieweg\Dce\Components\FlexformToTcaMapper\Mapper::getSql();
        }
        return [$sqlStrings];
    }

    /**
     * Checks if required fields are already in database.
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function checkRequiredFieldsExisting() : bool
    {
        $dbFields = DatabaseUtility::getDatabaseConnection()->admin_get_fields('tx_dce_domain_model_dcefield');
        return \array_key_exists('map_to', $dbFields) &&
               \array_key_exists('new_tca_field_name', $dbFields) &&
               \array_key_exists('new_tca_field_type', $dbFields);
    }
}
