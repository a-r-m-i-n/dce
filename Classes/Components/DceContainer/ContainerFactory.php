<?php

namespace T3\Dce\Components\DceContainer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */

use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * ContainerFactory
 * Builds DCE Containers, which wrap grouped DCEs.
 */
class ContainerFactory
{
    /**
     * @var array contains uids of content elements which can be skipped
     */
    protected static $toSkip = [];

    /**
     * @var array|int[] Caches uids of containers, in case contents are rendered multiple times on same page
     */
    protected static $cachedContainers = [];

    public static function makeContainer(Dce $dce, bool $includeHidden = false): Container
    {
        $contentObject = $dce->getContentObject();
        static::$toSkip[$contentObject['uid']][] = $contentObject['uid'];

        /** @var Container $container */
        $container = GeneralUtility::makeInstance(Container::class, $dce);

        $newsParameters = GeneralUtility::_GET('tx_news_pi1');
        if (isset($newsParameters['news']) && !empty($newsParameters['news'])) {
            // Content elements on news detail page
            $contentElements = static::getContentElementsInContainer($dce, $includeHidden, (int)$newsParameters['news']);
        } else {
            // Regular content elements
            $contentElements = static::getContentElementsInContainer($dce, $includeHidden);
        }

        $total = \count($contentElements);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var DceRepository $dceRepository */
        $dceRepository = $objectManager->get(DceRepository::class);
        foreach ($contentElements as $index => $contentElement) {
            $dceInstance = $dceRepository->getDceInstance((int)$contentElement['uid'], $contentElement);
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
        self::$cachedContainers[] = $contentObject['uid'];

        return $container;
    }

    /**
     * Get content elements rows of following content elements in current row.
     */
    protected static function getContentElementsInContainer(Dce $dce, bool $includeHidden = false, int $newsUid = 0): array
    {
        $queryBuilder = self::createQueryBuilder($includeHidden);

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

        if ($newsUid > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq(
                'tx_news_related_news',
                $queryBuilder->createNamedParameter($newsUid, \PDO::PARAM_INT)
            ));
        }

        if (Compatibility::isFrontendMode()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($contentObject['sys_language_uid'], \PDO::PARAM_INT)
                )
            );
        }

        if (ExtensionManagementUtility::isLoaded('gridelements')
            && '0' != $contentObject['tx_gridelements_container']
            && '0' != $contentObject['tx_gridelements_columns']
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

        if (ExtensionManagementUtility::isLoaded('container') && '0' != $contentObject['tx_container_parent']) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($contentObject['tx_container_parent'], \PDO::PARAM_INT)
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
            if ((
                $contentObject['uid'] !== $rawContentElement['uid'] &&
                 1 === $rawContentElement['tx_dce_new_container']
            )
                || $rawContentElement['CType'] !== $dce->getIdentifier()
            ) {
                return $contentElementsInContainer;
            }

            if ($rawContentElement['sys_language_uid'] > 0 && !empty($rawContentElement['l18n_parent']) && $rawContentElement['uid'] !== $rawContentElement['l18n_parent'] && !isset($rawContentElement['_LOCALIZED_UID'])) {
                // Make what PageRepository->versionOL would do
                $rawContentElement['_LOCALIZED_UID'] = $rawContentElement['uid'];
                $rawContentElement['uid'] = $rawContentElement['l18n_parent'];
            }

            $contentElementsInContainer[] = $rawContentElement;
        }

        return $contentElementsInContainer;
    }

    /**
     * Checks if DCE content element should be skipped instead of rendered.
     *
     * @param array|int|mixed $contentElement
     *
     * @return bool Returns true when this content element has been rendered already
     */
    public static function checkContentElementForBeingRendered($contentElement): bool
    {
        if (in_array((int)$contentElement['uid'], self::$cachedContainers, true)) {
            return false;
        }
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
     */
    public static function clearContentElementsToSkip($contentElement = null): void
    {
        if (null === $contentElement) {
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
            if (null !== $groupContentElementsIndex) {
                unset(static::$toSkip[$groupContentElementsIndex]);
            }
        }
    }

    /**
     * Resolves CType="shortcut" content elements.
     *
     * @param array $rawContentElements array with tt_content rows
     */
    protected static function resolveShortcutElements(array $rawContentElements): array
    {
        $resolvedContentElements = [];
        foreach ($rawContentElements as $rawContentElement) {
            if ('shortcut' === $rawContentElement['CType']) {
                // resolve records stored with "table_name:uid"
                $aLinked = explode(',', $rawContentElement['records']);
                foreach ($aLinked as $sLinkedEl) {
                    $iPos = strrpos($sLinkedEl, '_');
                    $table = (false !== $iPos) ? substr($sLinkedEl, 0, $iPos) : 'tt_content';
                    $uid = (false !== $iPos) ? substr($sLinkedEl, $iPos + 1) : '0';

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
     */
    protected static function createContainerIteratorArray(int $index, int $total): array
    {
        return [
            'isOdd' => 0 === $index % 2,
            'isEven' => 0 !== $index % 2,
            'isFirst' => 0 === $index,
            'isLast' => $index === $total - 1,
            'cycle' => $index + 1,
            'index' => $index,
            'total' => $total,
        ];
    }

    private static function createQueryBuilder(bool $includeHidden = false): QueryBuilder
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        if (Compatibility::isFrontendMode()) {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }
        if ($includeHidden) {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
        }

        $queryBuilder
            ->select('*')
            ->from('tt_content');

        return $queryBuilder;
    }
}
