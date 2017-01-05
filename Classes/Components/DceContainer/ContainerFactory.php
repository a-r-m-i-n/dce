<?php
namespace ArminVieweg\Dce\Components\DceContainer;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Model\Dce;
use ArminVieweg\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ContainerFactory
 * Builds DceContainers, which wrap grouped DCEs
 *
 * @package ArminVieweg\Dce
 */
class ContainerFactory
{
    /**
     * Contains uids of content elements which can be skipped
     *
     * @var array
     */
    protected static $contentElementsToSkip = [];

    /**
     * @param Dce $dce
     * @return Container
     */
    public static function makeContainer(Dce $dce)
    {
        $contentObject = $dce->getContentObject();
        static::$contentElementsToSkip[] = $contentObject['uid'];

        /** @var Container $container */
        $container = GeneralUtility::makeInstance(
            'ArminVieweg\Dce\Components\DceContainer\Container',
            $dce
        );

        $contentElements = static::getContentElementsInContainer($dce);
        $total = count($contentElements);
        foreach ($contentElements as $index => $contentElement) {
            try {
                /** @var \ArminVieweg\Dce\Domain\Model\Dce $dce */
                $dceInstance = clone \ArminVieweg\Dce\Utility\Extbase::bootstrapControllerAction(
                    'ArminVieweg',
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

            if (!in_array($contentElement['uid'], static::$contentElementsToSkip)) {
                static::$contentElementsToSkip[] = $contentElement['uid'];
            }

            if (!empty($contentElement['l18n_parent']) &&
                !in_array($contentElement['l18n_parent'], static::$contentElementsToSkip)
            ) {
                static::$contentElementsToSkip[] = $contentElement['l18n_parent'];
            }

            if (!empty($contentElement['_LOCALIZED_UID']) &&
                !in_array($contentElement['_LOCALIZED_UID'], static::$contentElementsToSkip)
            ) {
                static::$contentElementsToSkip[] = $contentElement['_LOCALIZED_UID'];
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
    protected static function getContentElementsInContainer(Dce $dce)
    {
        $contentObject = $dce->getContentObject();
        $sortColumn = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
        $deleteColumn = $GLOBALS['TCA']['tt_content']['ctrl']['delete'];
        $where = 'pid = ' . $contentObject['pid'] .
                 ' AND colPos = ' . $contentObject['colPos'] .
                 ' AND ' . $sortColumn . ' > ' . $contentObject[$sortColumn] .
                 ' AND uid != ' . $contentObject['uid'] .
                 ' AND ' . $deleteColumn . ' = 0' .
                 ' AND (starttime <= ' . (int) $GLOBALS['SIM_ACCESS_TIME'] . ' OR starttime = 0)' .
                 ' AND (endtime >= ' . (int) $GLOBALS['SIM_ACCESS_TIME'] . ' OR endtime = 0)';
        // TODO: Still not checking current frontend user permission if set

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')
            && $contentObject['tx_gridelements_container'] != '0'
        ) {
            $where .= ' AND tx_gridelements_container = ' . $contentObject['tx_gridelements_container'];
        }

        if (TYPO3_MODE === 'FE') {
            $where .= ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
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
            if ($rawContentElement['CType'] !== 'dce_dceuid' . $dce->getUid() ||
                ($contentObject['uid'] !== $rawContentElement['uid'] &&
                    $rawContentElement['tx_dce_new_container'] === '1')
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
    public static function checkContentElementForBeingRendered($contentElement)
    {
    	if (is_array($contentElement)) {
	        return in_array($contentElement['uid'], static::$contentElementsToSkip);
        } else if(is_integer($contentElement)) {
            return in_array($contentElement, static::$contentElementsToSkip);
        }
        return false;
    }

    /**
     * Clears the content elements to skip. This might be necessary if one page
     * should render the same content element twice (using reference e.g.).
     *
     * @return void
     */
    public static function clearContentElementsToSkip()
    {
        static::$contentElementsToSkip = [];
    }

    /**
     * Resolves CType="shortcut" content elements
     *
     * @param array $rawContentElements array with tt_content rows
     * @return array
     */
    protected static function resolveShortcutElements(array $rawContentElements)
    {
        $resolvedContentElements = [];
        foreach ($rawContentElements as $rawContentElement) {
            if ($rawContentElement['CType'] === 'shortcut') {
            	// resolve records stored with "table_name:uid"
                $aLinked = explode(",", $rawContentElement['records']);
                foreach ( $aLinked as $sLinkedEl){
                	$iPos = strrpos($sLinkedEl, "_");
                	$table = ($iPos!==false) ? substr($sLinkedEl, 0 , $iPos) : 'tt_content';
                	$uid = ($iPos!==false) ? substr($sLinkedEl, $iPos+1) : '0';

                    $linkedContentElements = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
                    	'*',
                    	$table,
                    	'uid = ' . $uid,
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
    protected static function createContainerIteratorArray($index, $total)
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
