<?php

namespace T3\Dce\UserFunction\CustomLabels;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\BackendView\SimpleBackendView;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;

/**
 * Extends TCA label of fields with variable key.
 */
class TtContentLabel
{
    private SimpleBackendView $simpleBackendView;

    public function __construct(SimpleBackendView $simpleBackendView)
    {
        $this->simpleBackendView = $simpleBackendView;
    }

    /**
     * User function to get custom labels for tt_content.
     * This is required, when content elements based on DCE use
     * the Simple Backend View.
     */
    public function getLabel(array &$parameter): void
    {
        if (!isset($parameter['row']) || !isset($parameter['row']['CType']) || !isset($parameter['row'][$GLOBALS['TCA']['tt_content']['ctrl']['label']])) {
            return;
        }
        if ((\is_string($parameter['row']['CType']) || is_array($parameter['row']['CType']))
            && $this->isDceContentElement($parameter['row'])
        ) {
            try {
                $dceUid = $parameter['row']['uid'] ?? null;
                if (!$dceUid && is_array($_GET['edit']['tt_content'])) {
                    $dceUid = array_keys($_GET['edit']['tt_content'])[0];
                }

                if (0 === $dceUid || ($_GET['edit']['tt_content'][$dceUid] ?? '') === 'new') {
                    $parameter['title'] = 'New DCE';

                    return;
                }

                if (null === $dceUid || $dceUid < 0) {
                    return;
                }

                /** @var Dce $dce */
                $dce = DatabaseUtility::getDceObjectForContentElement($dceUid, true);
            } catch (\Exception $exception) {
                $parameter['title'] = 'ERROR: ' . $exception->getMessage();

                return;
            }

            if ($dce->isUseSimpleBackendView()) {
                $headerContent = $this->simpleBackendView->getHeaderContent($dce, true);
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
        if (is_array($cType)) {
            // For any reason the CType can be an array with one entry
            $cType = reset($cType);
        }

        return str_starts_with($cType, 'dce_');
    }
}
