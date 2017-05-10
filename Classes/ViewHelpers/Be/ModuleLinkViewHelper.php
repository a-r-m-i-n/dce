<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This view helper returns a link to module in TYPO3 backend
 *
 * @package ArminVieweg\Dce
 */
class ModuleLinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Returns link to module
     *
     * @param string $module Name of module
     * @param string $parameter eg.: "edit[tx_dce_domain_model_dce][1]=edit&returnUrl="
     * @return string URL to backend module
     */
    public function render($module, $parameter)
    {
        $parameters = GeneralUtility::explodeUrl2Array($parameter);
        return BackendUtility::getModuleUrl($module, $parameters);
    }
}
