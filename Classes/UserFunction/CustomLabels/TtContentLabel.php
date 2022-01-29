<?php

namespace T3\Dce\UserFunction\CustomLabels;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\BackendView\SimpleBackendView;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Extends TCA label of fields with variable key.
 */
class TtContentLabel
{
    /**
     * User function to get custom labels for tt_content.
     * This is required, when content elements based on DCE use
     * the Simple Backend View.
     */
    public function getLabel(array &$parameter): void
    {
        if ((\is_string($parameter['row']['CType']) || \is_array($parameter['row']['CType'])) &&
            $this->isDceContentElement($parameter['row'])
        ) {
            try {
                /** @var Dce $dce */
                $dce = DatabaseUtility::getDceObjectForContentElement($parameter['row']['uid']);
            } catch (\Exception $exception) {
                $parameter['title'] = 'ERROR: ' . $exception->getMessage();

                return;
            }

            if ($dce->isUseSimpleBackendView()) {
                $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
                /** @var SimpleBackendView $simpleBackendViewUtility */
                $simpleBackendViewUtility = $objectManager->get(SimpleBackendView::class);
                $headerContent = $simpleBackendViewUtility->getHeaderContent($dce, true);
                if (!empty($headerContent)) {
                    $parameter['title'] = $headerContent;

                    return;
                }
            } else {
                $parameter['title'] = trim(strip_tags($dce->renderBackendTemplate('header')));

                return;
            }
        }
        $parameter['title'] = $parameter['row'][$GLOBALS['TCA']['tt_content']['ctrl']['label']];
    }

    /**
     * Checks if given tt_content row is a content element based on DCE.
     */
    protected function isDceContentElement(array $row): bool
    {
        $cType = $row['CType'];
        if (\is_array($cType)) {
            // For any reason the CType can be an array with one entry
            $cType = reset($cType);
        }

        return false !== strpos($cType, 'dce_');
    }
}
