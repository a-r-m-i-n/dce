<?php
namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\FlexformToTcaMapper\Mapper as TcaMapper;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\FlashMessage;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * AfterSave Hook
 */
class AfterSaveHook
{
    /** @var DataHandler */
    protected $dataHandler = null;

    /** @var int uid of current record */
    protected $uid = 0;

    /** @var array|null corresponding database row */
    protected $row;

    /** @var array all properties of current record */
    protected $fieldArray = [];

    /** @var array extension settings */
    protected $extConfiguration = [];

    /**
     * If variable in given fieldSettings is set, it will be returned.
     * Otherwise a new variableName will be returned, based on the type of the field.
     *
     * @param array $fieldSettings
     * @return string
     */
    protected function getVariableNameFromFieldSettings(array $fieldSettings) : string
    {
        if (!isset($fieldSettings['variable']) || empty($fieldSettings['variable'])) {
            switch ($fieldSettings['type']) {
                default:
                case 0:
                    return uniqid('field_');

                case 1:
                    return uniqid('tab_');

                case 2:
                    return uniqid('section_');
            }
        }
        return $fieldSettings['variable'];
    }

    // phpcs:disable

    /**
     * Hook action
     *
     * @param string $status
     * @param string $table
     * @param string|int $id
     * @param array $fieldArray
     * @param DataHandler $pObj
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $pObj
    ) : void {
        $this->extConfiguration = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce'],
            ['allowed_classes' => false]
        );
        $this->dataHandler = $pObj;
        $this->fieldArray = [];
        foreach ($fieldArray as $key => $value) {
            if (!empty($key)) {
                $this->fieldArray[$key] = $value;
            }
        }
        $this->uid = $this->getUid($id, $table, $status, $pObj);

        if ($table === 'tt_content') {
            $contentRow = $this->dataHandler->recordInfo('tt_content', $this->uid, '*');

            // Prevent "Copy (1)" suffix when copying tt_content based on DCE
            if ($dceUid = DceRepository::extractUidFromCTypeOrIdentifier($contentRow['CType'])) {
                $origUid = $contentRow['t3_origuid'];
                if ($origUid) {
                    $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $dceUid, '*');
                    if ($dceRow['prevent_header_copy_suffix']) {
                        $origRecord = $this->dataHandler->recordInfo('tt_content', $origUid, 'header');
                        $this->dataHandler->updateDB('tt_content', $this->uid, ['header' => $origRecord['header']]);
                    }
                }
            }

            // Write flexform values to TCA, when enabled
            if ($dceUid = DceRepository::extractUidFromCTypeOrIdentifier($contentRow['CType'])) {
                $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $dceUid, '*');
                $dceIdentifier = !empty($dceRow['identifier']) ? 'dce_' . $dceRow['identifier']
                    : 'dce_dceuid' . $dceUid;

                $this->checkAndUpdateDceRelationField($contentRow, $dceIdentifier);
                TcaMapper::saveFlexformValuesToTca(
                    [
                        'uid' => $this->uid,
                        'CType' => $dceIdentifier
                    ],
                    $this->fieldArray['pi_flexform']
                );
                unset($dceIdentifier);
            } else {
                // When a (formerly) DCE content element gets a different CType
                if ((int) $contentRow['tx_dce_dce'] !== 0) {
                    $this->dataHandler->updateDB('tt_content', $this->uid, ['tx_dce_dce' => 0]);
                }
            }
        }

        // When a DCE is disabled, also disable/hide the based content elements
        if ($table === 'tx_dce_domain_model_dce' && $status === 'update') {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
                if (array_key_exists('hidden', $fieldArray) && $fieldArray['hidden'] === '1') {
                    $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $this->uid, '*');
                    $dceIdentifier = !empty($dceRow['identifier']) ? 'dce_' . $dceRow['identifier']
                                                                            : 'dce_dceuid' . $this->uid;
                    $this->hideContentElementsBasedOnDce($dceIdentifier);
                    unset($dceRow, $dceIdentifier);
                }
            }
        }

        // Show hint when dcefield has been mapped to tca column
        if ($table === 'tx_dce_domain_model_dcefield' && $status === 'update') {
            if (array_key_exists('new_tca_field_name', $fieldArray) ||
                array_key_exists('new_tca_field_type', $fieldArray)
            ) {
                FlashMessage::add(
                    'You did some changes (in DceField with uid ' . $this->uid . ') which affects the sql schema of ' .
                    'tt_content table. Please don\'t forget to update database schema (in e.g. Install Tool)!',
                    'SQL schema changes detected!',
                    \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
                );
            }
        }

        // Adds or removes *containerflag from simple backend view, when container is en- or disabled
        if ($table === 'tx_dce_domain_model_dce' && ($status === 'update' || $status === 'new')) {
            $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $this->uid, '*');
            if (array_key_exists('enable_container', $fieldArray)) {
                if ($fieldArray['enable_container'] === '1') {
                    $items = GeneralUtility::trimExplode(',', $dceRow['backend_view_bodytext'], true);
                    $items[] = '*containerflag';
                } else {
                    $items = ArrayUtility::removeArrayEntryByValue(
                        GeneralUtility::trimExplode(',', $dceRow['backend_view_bodytext'], true),
                        '*containerflag'
                    );
                }
                $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dce');
                $connection->update(
                    'tx_dce_domain_model_dce',
                    [
                        'backend_view_bodytext' => implode(',', $items)
                    ],
                    [
                        'uid' => $this->uid
                    ]
                );
            }
        }
    }

    // phpcs:enable

    /**
     * Disables content elements based on this deactivated DCE. Also display flash message
     * about the amount of content elements affected and a notice, that these content elements
     * will not get re-enabled when enabling the DCE again.
     *
     * @param string $dceIdentifier
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function hideContentElementsBasedOnDce(string $dceIdentifier)
    {
        $updatedContentElementsCount = 0;
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        $statement = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter($dceIdentifier, \PDO::PARAM_STR)
                )
            )
            ->execute();

        while ($row = $statement->fetch()) {
            $this->dataHandler->updateDB('tt_content', $row['uid'], ['hidden' => 1]);
            $updatedContentElementsCount++;
        }

        if ($updatedContentElementsCount === 0) {
            return;
        }

        $pathToLocallang = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xml:';
        $message = LocalizationUtility::translate(
            $pathToLocallang . 'hideContentElementsBasedOnDce',
            'Dce',
            ['count' => $updatedContentElementsCount]
        );
        FlashMessage::add(
            $message,
            LocalizationUtility::translate($pathToLocallang . 'caution', 'Dce'),
            AbstractMessage::INFO
        );
    }

    /**
     * Get tx_dce_dce of current tt_content pObj instance
     *
     * @param DataHandler $pObj
     * @return int
     */
    protected function getDceUid(DataHandler $pObj) : int
    {
        $datamap = $pObj->datamap;
        $datamap = reset($datamap);
        $datamap = reset($datamap);
        return DceRepository::extractUidFromCTypeOrIdentifier($datamap['CType']);
    }

    /**
     * Checks the CType of current content element and return TRUE if it is a dce. Otherwise return FALSE.
     *
     * @param DataHandler $pObj
     * @return bool
     */
    protected function isDceContentElement(DataHandler $pObj)
    {
        return (bool) $this->getDceUid($pObj);
    }

    /**
     * Investigates the uid of entry
     *
     * @param $id
     * @param $table
     * @param $status
     * @param $pObj
     * @return int
     */
    protected function getUid($id, $table, $status, $pObj)
    {
        $uid = $id;
        if ($status === 'new') {
            if (!$pObj->substNEWwithIDs[$id]) {
                //postProcessFieldArray
                $uid = 0;
            } else {
                //afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }
        return (int) $uid;
    }

    /**
     * Check if tx_dce_dce matches given dce identifier. Update if not.
     *
     * @param string $dceIdentifier
     * @return void
     */
    protected function checkAndUpdateDceRelationField(array $contentRow, string $dceIdentifier)
    {
        $dceUid = DceRepository::extractUidFromCTypeOrIdentifier($dceIdentifier);
        if ($dceUid && $dceUid !== (int) $contentRow['tx_dce_dce']) {
            $this->dataHandler->updateDB('tt_content', $this->uid, ['tx_dce_dce' => $dceUid]);
        }
    }
}
