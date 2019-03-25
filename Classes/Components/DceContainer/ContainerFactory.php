<?php
namespace T3\Dce\Components\DceContainer;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
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
     * @return Container
     */
    public static function makeContainer(Dce $dce) : Container
    {
        $contentObject = $dce->getContentObject();
        static::$toSkip[$contentObject['uid']][] = $contentObject['uid'];

        /** @var Container $container */
        $container = GeneralUtility::makeInstance(Container::class, $dce);

        $contentElements = static::getContentElementsInContainer($dce);
        $total = \count($contentElements);
        foreach ($contentElements as $index => $contentElement) {
            try {
                /** @var \T3\Dce\Domain\Model\Dce $dce */
                $dceInstance = clone \T3\Dce\Utility\Extbase::bootstrapControllerAction(
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
     * @return array
     */
    protected static function getContentElementsInContainer(Dce $dce) : array
    {
        $contentObject = $dce->getContentObject();
        $sortColumn = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
        $where = 'pid = ' . $contentObject['pid'] .
                 ' AND colPos = ' . $contentObject['colPos'] .
                 ' AND ' . $sortColumn . ' > ' . $contentObject[$sortColumn] .
                 ' AND uid != ' . $contentObject['uid'];

        if (TYPO3_MODE === 'FE') {
            $where .= ' AND sys_language_uid = ' . Compatibility::getSysLanguageUid();
            $where .= DatabaseUtility::getEnabledFields('tt_content');
        } else {
            $where .= DatabaseUtility::deleteClause('tt_content');
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')
            && $contentObject['tx_gridelements_container'] != '0'
            && $contentObject['tx_gridelements_columns'] != '0'
        ) {
            $where .= ' AND tx_gridelements_container = ' . $contentObject['tx_gridelements_container'];
            $where .= ' AND tx_gridelements_columns = ' . $contentObject['tx_gridelements_columns'];
        }

        if (TYPO3_MODE === 'FE') {
            $where .= ' AND sys_language_uid = ' . Compatibility::getSysLanguageUid();
        }

        $rawContentElements = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tt_content',
            $where,
            '',
            $sortColumn . ' asc',
            $dce->getContainerItemLimit() ? $dce->getContainerItemLimit() - 1 : ''
        );
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

                    $linkedContentElements = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
                        '*',
                        $table,
                        'uid = ' . $uid . ' AND hidden=0 AND deleted=0',
                        '',
                        $GLOBALS['TCA'][$table]['ctrl']['sortby'] . ' asc'
                    );
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
