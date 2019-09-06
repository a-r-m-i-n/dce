<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Migrate Flexform sheet identifier
 *
 * In the past DCE named tabs in flexform configuration like this:
 * <sheet0></sheet0>
 *
 * But this has the effect that all your data is broken, when you change
 * the order of tabs in a DCE. Now the sheets have a named identifier. You
 * can set the identifier in the variable field which is also visible for
 * tab fields, now.
 *
 * The flexform configuration looks like this now:
 * <sheet.tabGeneral></sheet.tabGeneral>
 *
 * The very first sheet has the identifier/variable "tabGeneral" by default.
 *
 * Please migrate the field database relations first, before executing this update!
 */
class MigrateFlexformSheetIdentifierUpdate extends AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate flexform sheet identifiers';

    /**
     * @var string
     */
    protected $identifier = 'dceMigrateFlexformSheetIdentifierUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $contentElementsWithWrongXml = $this->getUpdatableContentElements();

        $description .= 'There are <b>' . \count($tabsWithoutIdentifier) . ' tab fields</b> without identifier and ' .
                        '<b>' . \count($contentElementsWithWrongXml) . ' content elements</b> with old xml structure ' .
                        'existing.<br>' .
                        'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
                        'before executing this update wizard.<br><br>';

        return \count($tabsWithoutIdentifier) > 0 || \count($contentElementsWithWrongXml) > 0;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string|array &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     * @TODO Refactor me
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $tabsGroupedByDce = [];
        foreach ($tabsWithoutIdentifier as $tabRow) {
            $tabRow['variable'] = 'tab' . $this->convertFieldTitleToVariable($tabRow['title']);
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                [
                    'variable' => $tabRow['variable']
                ],
                [
                    'uid' => $tabRow['uid']
                ]
            );

            if (!isset($tabsGroupedByDce[$tabRow['parent_dce']])) {
                $tabsGroupedByDce[$tabRow['parent_dce']] = [];
                if ($tabRow['sorting'] !== '1') {
                    $tabsGroupedByDce[$tabRow['parent_dce']][] = ['variable' => 'tabGeneral'];
                }
            }
            $tabsGroupedByDce[$tabRow['parent_dce']][] = $tabRow;
        }

        /** @var FlexFormTools $flexFormTools */
        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        foreach ($tabsGroupedByDce as $dceUid => $tabs) {
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
            $contentElements = $queryBuilder
                ->select('*')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter($this->getDceIdentifier($dceUid), \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetchAll();

            foreach ($contentElements as $contentElement) {
                $flexformData = GeneralUtility::xml2array($contentElement['pi_flexform']);
                $i = 0;
                $newFlexformData = ['data' => []];
                if (!empty($flexformData['data'])) {
                    foreach ($flexformData['data'] as $sheetIdentifier => $sheetData) {
                        if ($i === 0) {
                            // First sheet
                            $newFlexformData['data']['sheet.tabGeneral'] = $sheetData;
                            $i++;
                            continue;
                        }
                        if (strpos($sheetIdentifier, '.') === false) {
                            $newFlexformData['data']['sheet.' . $tabs[$i]['variable']] = $sheetData;
                        } else {
                            $newFlexformData['data'][$sheetIdentifier] = $sheetData;
                        }
                        $i++;
                    }
                }

                $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');
                $connection->update(
                    'tt_content',
                    [
                        'pi_flexform' => $flexFormTools->flexArray2Xml($newFlexformData, true)
                    ],
                    [
                        'uid' => (int) $contentElement['uid']
                    ]
                );
            }
            unset($connection);
        }

        $updatableContentElements = $this->getUpdatableContentElements();
        foreach ($updatableContentElements as $contentElement) {
            $flexformData = GeneralUtility::xml2array($contentElement['pi_flexform']);
            $newFlexformData = ['data' => []];
            if (!empty($flexformData['data'])) {
                foreach ($flexformData['data'] as $sheetIdentifier => $sheetData) {
                    if ($sheetIdentifier === 'sheet0') {
                        $newFlexformData['data']['sheet.tabGeneral'] = $sheetData;
                    } else {
                        $newFlexformData['data'][$sheetIdentifier] = $sheetData;
                    }
                }
            }
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                [
                    'pi_flexform' => $flexFormTools->flexArray2Xml($newFlexformData, true)
                ],
                [
                    'uid' => (int) $contentElement['uid']
                ]
            );
        }
        return true;
    }

    /**
     * Returns tabs (DceFields with type=1) without set variable field
     *
     * @return array DceField rows
     */
    protected function getUpdatableDceFields() : array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        return $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'type',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                ),
                'variable = ""'
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
    }

    /**
     * Returns content elements, based on DCE, with old sheet identifier
     *
     * @return array tt_content rows
     */
    protected function getUpdatableContentElements() : array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like(
                    'CType',
                    $queryBuilder->createNamedParameter('dce_%')
                ),
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter('%<sheet index=\"sheet0\">%')
                )
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * Converts given fieldTitle to variable name (in UpperCamelCase).
     *
     * @param string $fieldTitle The title you want to convert
     * @return string The converted variable name in UpperCamelCase
     */
    protected function convertFieldTitleToVariable(string $fieldTitle) : string
    {
        /** @var CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
        $variable = $charsetConverter->specCharsToASCII('utf-8', $fieldTitle);
        $variable = preg_replace('/[^A-Z0-9]/i', '_', $variable);
        return GeneralUtility::underscoredToUpperCamelCase($variable);
    }
}
