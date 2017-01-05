<?php
namespace ArminVieweg\Dce\UserFunction\CustomLabels;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Repository\DceRepository;

/**
 * Extends TCA label of fields with variable key
 *
 * @package ArminVieweg\Dce
 */
class TtContentLabel
{
    /**
     * User function to get custom labels for tt_content.
     * This is required, when content elements based on DCE use
     * the Simple Backend View.
     *
     * @param array $parameter
     * @return void
     */
    public function getLabel(&$parameter)
    {
        if ((is_string($parameter['row']['CType']) || is_array($parameter['row']['CType'])) &&
            $this->isDceContentElement($parameter['row'])
        ) {
            try {
                /** @var \ArminVieweg\Dce\Domain\Model\Dce $dce */
                $dce = \ArminVieweg\Dce\Utility\Extbase::bootstrapControllerAction(
                    'ArminVieweg',
                    'Dce',
                    'Dce',
                    'renderDce',
                    'Dce',
                    [
                        'contentElementUid' => $parameter['row']['uid'],
                        'dceUid' => DceRepository::extractUidFromCtype($parameter['row']['CType'])
                    ],
                    true
                );
            } catch (\Exception $exception) {
                $parameter['title'] = 'ERROR: ' . $exception->getMessage();
                return;
            }

            if ($dce->isUseSimpleBackendView()) {
                $simpleBackendViewUtility = new \ArminVieweg\Dce\Components\BackendView\SimpleBackendView();
                $parameter['title'] = $simpleBackendViewUtility->getHeaderContent($dce, true);
                return;
            } else {
                $parameter['title'] = trim(strip_tags($dce->renderBackendTemplate('header')));
                return;
            }
        }
        $parameter['title'] = $parameter['row'][$GLOBALS['TCA']['tt_content']['ctrl']['label']];
    }

    /**
     * Checks if given tt_content row is a content element based on DCE
     *
     * @param array $row
     * @return bool
     */
    protected function isDceContentElement(array $row)
    {
        $cType = $row['CType'];
        if (is_array($cType)) {
            // For any reason the CType can be an array with one entry
            $cType = reset($cType);
        }
        return strpos($cType, 'dce_dceuid') !== false;
    }
}
