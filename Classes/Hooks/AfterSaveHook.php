<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */

use Doctrine\DBAL\Driver\Statement;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use T3\Dce\Components\DetailPage\EmptySlugException;
use T3\Dce\Components\DetailPage\SlugGenerator;
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
 * AfterSave Hook.
 */
class AfterSaveHook
{
    private const LLL = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:';

    /** @var DataHandler */
    protected $dataHandler = null;

    /** @var int uid of current record */
    protected $uid = 0;

    /** @var array|null corresponding database row */
    protected $row;

    /** @var array all properties of current record */
    protected $fieldArray = [];

    /**
     * If variable in given fieldSettings is set, it will be returned.
     * Otherwise a new variableName will be returned, based on the type of the field.
     */
    protected function getVariableNameFromFieldSettings(array $fieldSettings): string
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
     * Hook action.
     *
     * @param string|int $id
     * @TODO This method should get entirely refactored
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $pObj
    ): void {
        $this->dataHandler = $pObj;
        $this->fieldArray = [];
        foreach ($fieldArray as $key => $value) {
            if (!empty($key)) {
                $this->fieldArray[$key] = $value;
            }
        }
        $this->uid = $this->getUid($id, $table, $status, $pObj);

        if ('tt_content' === $table) {
            $contentRow = $this->dataHandler->recordInfo('tt_content', $this->uid, '*');

            // Prevent "Copy (1)" suffix when copying tt_content based on DCE
            if ($dceUid = DceRepository::extractUidFromCTypeOrIdentifier($contentRow['CType'])) {
                $origUid = $contentRow['t3_origuid'];
                if ($origUid) {
                    $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $dceUid, '*');
                    if ($dceRow['prevent_header_copy_suffix'] && 'new' === $status) {
                        $origRecord = $this->dataHandler->recordInfo('tt_content', $origUid, 'header');
                        $this->dataHandler->updateDB('tt_content', $this->uid, ['header' => $origRecord['header']]);
                    }
                }
            }

            $dceUid = DceRepository::extractUidFromCTypeOrIdentifier($contentRow['CType']);
            // Write flexform values to TCA, when enabled
            if ($dceUid) {
                $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $dceUid, '*');
                $dceIdentifier = !empty($dceRow['identifier']) ? 'dce_' . $dceRow['identifier']
                    : 'dce_dceuid' . $dceUid;

                $this->checkAndUpdateDceRelationField($contentRow, $dceIdentifier);
                TcaMapper::saveFlexformValuesToTca(
                    [
                        'uid' => $this->uid,
                        'CType' => $dceIdentifier,
                    ],
                    $this->fieldArray['pi_flexform'] ?? []
                );
                unset($dceIdentifier);
            } else {
                // When a (formerly) DCE content element gets a different CType
                if (0 !== (int)$contentRow['tx_dce_dce']) {
                    $this->dataHandler->updateDB('tt_content', $this->uid, ['tx_dce_dce' => 0]);
                }
            }
            // Generate slug, when enabled
            if ($dceUid) {
                $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $dceUid, '*');
                if (!empty($dceRow['detailpage_slug_expression'])) {
                    /** @var SlugGenerator $generator */
                    $generator = GeneralUtility::makeInstance(SlugGenerator::class);
                    $slug = null;
                    try {
                        $slug = $generator->makeSlug($this->uid, $contentRow['pid'], $dceRow['detailpage_slug_expression']);
                    } catch (SyntaxError $e) {
                        FlashMessage::add(
                            LocalizationUtility::translate(self::LLL . 'slugUpdateFailed', 'Dce', [$e->getMessage()]),
                            LocalizationUtility::translate(self::LLL . 'caution', 'Dce'),
                            AbstractMessage::ERROR
                        );
                    } catch (EmptySlugException $e) {
                        $this->dataHandler->updateDB('tt_content', $this->uid, [
                            'tx_dce_slug' => $this->uid,
                        ]);
                        FlashMessage::add(
                            LocalizationUtility::translate(self::LLL . 'unableToGenerateSlug', 'Dce'),
                            LocalizationUtility::translate(self::LLL . 'caution', 'Dce'),
                            AbstractMessage::WARNING
                        );
                    }
                    if ($slug) {
                        if (!$slug->wasUnique()) {
                            FlashMessage::add(
                                LocalizationUtility::translate(self::LLL . 'slugNotUnique', 'Dce', [$slug]),
                                LocalizationUtility::translate(self::LLL . 'caution', 'Dce'),
                                AbstractMessage::NOTICE
                            );
                        }
                        $this->dataHandler->updateDB('tt_content', $this->uid, [
                            'tx_dce_slug' => $slug,
                        ]);
                    }
                }
            }
        }

        // When a DCE is disabled, also disable/hide the based content elements
        if ('tx_dce_domain_model_dce' === $table && 'update' === $status) {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
                if (array_key_exists('hidden', $fieldArray) && '1' === $fieldArray['hidden']) {
                    $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $this->uid, '*');
                    $dceIdentifier = !empty($dceRow['identifier']) ? 'dce_' . $dceRow['identifier']
                                                                            : 'dce_dceuid' . $this->uid;
                    $this->hideContentElementsBasedOnDce($dceIdentifier);
                    unset($dceRow, $dceIdentifier);
                }
            }
        }

        // Show hint when dcefield has been mapped to tca column
        if ('tx_dce_domain_model_dcefield' === $table && 'update' === $status) {
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

        if ('tx_dce_domain_model_dce' === $table && ('update' === $status || 'new' === $status)) {
            $dceRow = $this->dataHandler->recordInfo('tx_dce_domain_model_dce', $this->uid, '*');

            // Adds or removes *containerflag from simple backend view, when container is en- or disabled
            if (array_key_exists('enable_container', $fieldArray)) {
                if ('1' === $fieldArray['enable_container']) {
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
                        'backend_view_bodytext' => implode(',', $items),
                    ],
                    [
                        'uid' => $this->uid,
                    ]
                );
            }

            // Update slug for existing content elements
            if (array_key_exists('detailpage_slug_expression', $fieldArray)) {
                $dceIdentifier = !empty($dceRow['identifier']) ? 'dce_' . $dceRow['identifier']
                    : 'dce_dceuid' . $this->uid;
                $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
                /** @var Statement $statement */
                $statement = $queryBuilder
                    ->select('uid', 'pid')
                    ->from('tt_content')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'CType',
                            $queryBuilder->createNamedParameter($dceIdentifier, \PDO::PARAM_STR)
                        )
                    )
                    ->execute();

                if ($statement->rowCount() > 0) {
                    $generator = GeneralUtility::makeInstance(SlugGenerator::class);
                    while ($contentRow = $statement->fetch()) {
                        $slug = null;
                        if (!empty($dceRow['detailpage_slug_expression'])) {
                            try {
                                $slug = $generator->makeSlug(
                                    $contentRow['uid'],
                                    $contentRow['pid'],
                                    $dceRow['detailpage_slug_expression']
                                );
                            } catch (SyntaxError $e) {
                                FlashMessage::add(
                                    LocalizationUtility::translate(self::LLL . 'slugsUpdateFailed', 'Dce', [$e->getMessage()]),
                                    LocalizationUtility::translate(self::LLL . 'error', 'Dce'),
                                    AbstractMessage::ERROR
                                );

                                return;
                            } catch (EmptySlugException $e) {
                                $this->dataHandler->updateDB('tt_content', $contentRow['uid'], [
                                    'tx_dce_slug' => $contentRow['uid'],
                                ]);
                                FlashMessage::add(
                                    LocalizationUtility::translate(self::LLL . 'emptySlugGenerated', 'Dce', [$contentRow['uid'], $contentRow['pid']]),
                                    LocalizationUtility::translate(self::LLL . 'caution', 'Dce'),
                                    AbstractMessage::WARNING
                                );
                            }
                            if ($slug) {
                                $this->dataHandler->updateDB('tt_content', $contentRow['uid'], [
                                    'tx_dce_slug' => $slug,
                                ]);
                            }
                        } else {
                            $this->dataHandler->updateDB('tt_content', $contentRow['uid'], ['tx_dce_slug' => '']);
                        }
                    }

                    FlashMessage::add(
                        LocalizationUtility::translate(self::LLL . 'slugsUpdated', 'Dce', [$statement->rowCount()]),
                        LocalizationUtility::translate(self::LLL . 'info', 'Dce'),
                        AbstractMessage::NOTICE
                    );
                }
            }
        }

        if ('tx_dce_domain_model_dce' === $table && 'new' === $status && isset($fieldArray['t3_origuid']) && !empty($fieldArray['t3_origuid'])) {
            $this->dataHandler->updateDB('tx_dce_domain_model_dce', $this->uid, ['title' => $fieldArray['title'] . ' copy']);
        }
    }

    // phpcs:enable

    /**
     * Disables content elements based on this deactivated DCE. Also display flash message
     * about the amount of content elements affected and a notice, that these content elements
     * will not get re-enabled when enabling the DCE again.
     *
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function hideContentElementsBasedOnDce(string $dceIdentifier): void
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
            ++$updatedContentElementsCount;
        }

        if (0 === $updatedContentElementsCount) {
            return;
        }

        $message = LocalizationUtility::translate(
            self::LLL . 'hideContentElementsBasedOnDce',
            'Dce',
            ['count' => $updatedContentElementsCount]
        );
        FlashMessage::add(
            $message,
            LocalizationUtility::translate(self::LLL . 'caution', 'Dce'),
            AbstractMessage::INFO
        );
    }

    /**
     * Get tx_dce_dce of current tt_content pObj instance.
     */
    protected function getDceUid(DataHandler $pObj): int
    {
        $datamap = $pObj->datamap;
        $datamap = reset($datamap);
        $datamap = reset($datamap);

        return DceRepository::extractUidFromCTypeOrIdentifier($datamap['CType']);
    }

    /**
     * Checks the CType of current content element and return TRUE if it is a dce. Otherwise return FALSE.
     */
    protected function isDceContentElement(DataHandler $pObj): bool
    {
        return (bool)$this->getDceUid($pObj);
    }

    /**
     * Investigates the uid of entry.
     *
     * @param string|int $id
     */
    protected function getUid($id, string $table, string $status, DataHandler $pObj): int
    {
        $uid = $id;
        if ('new' === $status) {
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

        return (int)$uid;
    }

    /**
     * Check if tx_dce_dce matches given dce identifier. Update if not.
     */
    protected function checkAndUpdateDceRelationField(array $contentRow, string $dceIdentifier): void
    {
        $dceUid = DceRepository::extractUidFromCTypeOrIdentifier($dceIdentifier);
        if ($dceUid && $dceUid !== (int)$contentRow['tx_dce_dce']) {
            $this->dataHandler->updateDB('tt_content', $this->uid, ['tx_dce_dce' => $dceUid]);
        }
    }
}
