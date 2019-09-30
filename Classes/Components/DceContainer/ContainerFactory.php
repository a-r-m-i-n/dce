<?php
namespace T3\Dce\Components\DceContainer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\Extbase;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ContainerFactory
 * Builds DCE Containers, which wrap grouped DCEs
 */
class ContainerFactory
{
    /**
     * Contains uids of content elements which can be skipped
     *
     * @var array
     */
    protected static $toSkip = [];

    /**
     * @param Dce $dce
     * @param bool $includeHidden
     * @return Container
     */
    public static function makeContainer(Dce $dce, bool $includeHidden = false) : Container
    {
        $contentObject = $dce->getContentObject();
        static::$toSkip[$contentObject['uid']][] = $contentObject['uid'];

        /** @var Container $container */
        $container = GeneralUtility::makeInstance(Container::class, $dce);

        $contentElements = static::getContentElementsInContainer($dce, $includeHidden);
        $total = \count($contentElements);
        foreach ($contentElements as $index => $contentElement) {
            try {
                /** @var Dce $dceInstance */
                $dceInstance = clone Extbase::bootstrapControllerAction(
                    'T3',
                    'Dce',
                    'Dce',
                    'renderDce',
                    'Dce',
                    [
                        'contentElementUid' => $contentElement['uid'],
                        'dceUid' => $dce->getUid()
                    ],
                    true
                );
            } catch (\Exception $exception) {
                continue;
            }
            $dceInstance->setContainerIterator(static::createContainerIteratorArray($index, $total));
            $container->addDce($dceInstance);

            if (!\in_array($contentElement['uid'], static::$toSkip[$contentObject['uid']])) {
                static::$toSkip[$contentObject['uid']][] = $contentElement['uid'];
            }

            if (!empty($contentElement['l18n_parent']) &&
                !\in_array($contentElement['l18n_parent'], static::$toSkip[$contentObject['uid']])
            ) {
                static::$toSkip[$contentObject['uid']][] = $contentElement['l18n_parent'];
            }

            if (!empty($contentElement['_LOCALIZED_UID']) &&
                !\in_array($contentElement['_LOCALIZED_UID'], static::$toSkip[$contentObject['uid']])
            ) {
                static::$toSkip[$contentObject['uid']][] = $contentElement['_LOCALIZED_UID'];
            }
        }
        return $container;
    }

    /**
     * Get content elements rows of following content elements in current row
     *
     * @param Dce $dce
     * @param bool $includeHidden
     * @return array
     */
    protected static function getContentElementsInContainer(Dce $dce, bool $includeHidden = false) : array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        if ($includeHidden) {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
        }

        $queryBuilder
            ->select('*')
            ->from('tt_content');

        $contentObject = $dce->getContentObject();
        $sortColumn = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];

        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                'pid',
                $queryBuilder->createNamedParameter($contentObject['pid'], \PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->eq(
                'colPos',
                $queryBuilder->createNamedParameter($contentObject['colPos'], \PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->gt(
                $sortColumn,
                $contentObject[$sortColumn]
            ),
            $queryBuilder->expr()->neq(
                'uid',
                $queryBuilder->createNamedParameter($contentObject['uid'], \PDO::PARAM_INT)
            )
        );

        if (TYPO3_MODE === 'FE') {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(Compatibility::getSysLanguageUid(), \PDO::PARAM_INT)
                )
            );
        }

        if (ExtensionManagementUtility::isLoaded('gridelements')
            && $contentObject['tx_gridelements_container'] != '0'
            && $contentObject['tx_gridelements_columns'] != '0'
        ) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'tx_gridelements_container',
                    $queryBuilder->createNamedParameter($contentObject['tx_gridelements_container'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tx_gridelements_columns',
                    $queryBuilder->createNamedParameter($contentObject['tx_gridelements_columns'], \PDO::PARAM_INT)
                )
            );
        }

        if ($dce->getContainerItemLimit()) {
            $queryBuilder->setMaxResults($dce->getContainerItemLimit() - 1);
        }
        $rawContentElements = $queryBuilder
            ->orderBy($sortColumn, 'ASC')
            ->execute()
            ->fetchAll();

        array_unshift($rawContentElements, $contentObject);

        $resolvedContentElements = static::resolveShortcutElements($rawContentElements);

        $contentElementsInContainer = [];
        foreach ($resolvedContentElements as $rawContentElement) {
            if (($contentObject['uid'] !== $rawContentElement['uid'] &&
                 $rawContentElement['tx_dce_new_container'] === 1
                )
                || $rawContentElement['CType'] !== $dce->getIdentifier()
            ) {
                return $contentElementsInContainer;
            }
            $contentElementsInContainer[] = $rawContentElement;
        }
        return $contentElementsInContainer;
    }

    /**
     * Checks if DCE content element should be skipped instead of rendered.
     *
     * @param array|int $contentElement
     * @return bool Returns true when this content element has been rendered already
     */
    public static function checkContentElementForBeingRendered($contentElement) : bool
    {
        $flattenContentElementsToSkip = iterator_to_array(
            new \RecursiveIteratorIterator(new \RecursiveArrayIterator(static::$toSkip)),
            false
        );
        if (\is_array($contentElement)) {
            return \in_array($contentElement['uid'], $flattenContentElementsToSkip);
        }
        if (\is_int($contentElement)) {
            return \in_array($contentElement, $flattenContentElementsToSkip);
        }
        return false;
    }

    /**
     * Clears the content elements to skip. This might be necessary if one page
     * should render the same content element twice (using reference e.g.).
     *
     * @param int|array|null $contentElement
     * @return void
     */
    public static function clearContentElementsToSkip($contentElement = null) : void
    {
        if ($contentElement === null) {
            static::$toSkip = [];
        } else {
            $groupContentElementsIndex = null;
            foreach (static::$toSkip as $parentIndex => $groupedContentElementsToSkip) {
                if (\is_array($contentElement)) {
                    if (\end($groupedContentElementsToSkip) === $contentElement['uid']) {
                        $groupContentElementsIndex = $parentIndex;
                        break;
                    }
                } elseif (\is_int($contentElement)) {
                    if (\end($groupedContentElementsToSkip) === $contentElement) {
                        $groupContentElementsIndex = $parentIndex;
                        break;
                    }
                }
                reset($groupedContentElementsToSkip);
            }
            if ($groupContentElementsIndex !== null) {
                unset(static::$toSkip[$groupContentElementsIndex]);
            }
        }
    }

    /**
     * Resolves CType="shortcut" content elements
     *
     * @param array $rawContentElements array with tt_content rows
     * @return array
     */
    protected static function resolveShortcutElements(array $rawContentElements) : array
    {
        $resolvedContentElements = [];
        foreach ($rawContentElements as $rawContentElement) {
            if ($rawContentElement['CType'] === 'shortcut') {
                // resolve records stored with "table_name:uid"
                $aLinked = explode(',', $rawContentElement['records']);
                foreach ($aLinked as $sLinkedEl) {
                    $iPos = strrpos($sLinkedEl, '_');
                    $table = ($iPos !== false) ? substr($sLinkedEl, 0, $iPos) : 'tt_content';
                    $uid = ($iPos !== false) ? substr($sLinkedEl, $iPos + 1) : '0';

                    $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable($table);
                    $linkedContentElements = $queryBuilder
                        ->select('*')
                        ->from($table)
                        ->where(
                            $queryBuilder->expr()->eq(
                                'uid',
                                $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                            )
                        )
                        ->orderBy($GLOBALS['TCA'][$table]['ctrl']['sortby'], 'ASC')
                        ->execute()
                        ->fetchAll();

                    foreach ($linkedContentElements as $linkedContentElement) {
                        $resolvedContentElements[] = $linkedContentElement;
                    }
                }
            } else {
                $resolvedContentElements[] = $rawContentElement;
            }
        }
        return $resolvedContentElements;
    }

    /**
     * Creates iteration array, like fluid's ForViewHelper does.
     *
     * @param int $index starting with 0
     * @param int $total total amount of DCEs in container
     * @return array
     */
    protected static function createContainerIteratorArray(int $index, int $total) : array
    {
        return [
            'isOdd' => $index % 2 === 0,
            'isEven' => $index % 2 !== 0,
            'isFirst' => $index === 0,
            'isLast' => $index === $total - 1,
            'cycle' => $index + 1,
            'index' => $index,
            'total' => $total
        ];
    }
}
