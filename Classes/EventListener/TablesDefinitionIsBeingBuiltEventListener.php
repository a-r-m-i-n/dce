<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class TablesDefinitionIsBeingBuiltEventListener
{
    /**
     * Checks if required fields are already in database.
     */
    protected function checkRequiredFieldsExisting(): bool
    {
        $dbFields = DatabaseUtility::adminGetFields('tx_dce_domain_model_dcefield');

        return \array_key_exists('map_to', $dbFields)
               && \array_key_exists('new_tca_field_name', $dbFields)
               && \array_key_exists('new_tca_field_type', $dbFields);
    }

    public function addSchema(AlterTableDefinitionStatementsEvent $event): void
    {
        if ($this->checkRequiredFieldsExisting()) {
            $event->addSqlData(Mapper::getSql());
        }
    }
}
