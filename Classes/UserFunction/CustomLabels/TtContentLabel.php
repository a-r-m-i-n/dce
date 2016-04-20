<?php
namespace ArminVieweg\Dce\UserFunction\CustomLabels;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
        if (is_string($parameter['row']['CType']) &&
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
                    array(
                        'contentElementUid' => $parameter['row']['uid'],
                        'dceUid' => DceRepository::extractUidFromCtype($parameter['row']['CType'])
                    ),
                    true
                );
            } catch (\Exception $exception) {
                $parameter['title'] = 'ERROR: ' . $exception->getMessage();
                return;
            }

            if ($dce->isUseSimpleBackendView()) {
                $simpleBackendViewUtility = new \ArminVieweg\Dce\Components\SimpleBackendView\SimpleBackendView();
                $parameter['title'] = $simpleBackendViewUtility->getSimpleBackendViewHeaderContent($dce, true);
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
        return strpos($row['CType'], 'dce_dceuid') !== false;
    }
}
