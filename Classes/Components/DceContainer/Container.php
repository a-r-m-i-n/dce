<?php
namespace ArminVieweg\Dce\Components\DceContainer;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Model\Dce;
use ArminVieweg\Dce\Utility\PageTS;

/**
 * Container
 * The DCE Container
 *
 * @package ArminVieweg\Dce
 */
class Container
{
    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected $view;

    /**
     * @var Dce
     */
    protected $firstDce;

    /**
     * @var Dce[]
     */
    protected $dces = [];

    /**
     * Container constructor
     *
     * @param Dce $firstDceInContainer
     */
    public function __construct(Dce $firstDceInContainer)
    {
        $this->firstDce = $firstDceInContainer;
        $this->view = $firstDceInContainer->getFluidStandaloneView(Dce::TEMPLATE_FIELD_CONTAINER);
    }

    /**
     * Adds DceModel which contains the content element data
     *
     * @param Dce $dce
     * @return void
     */
    public function addDce(Dce $dce)
    {
        $contentObject = $dce->getContentObject();
        if ($contentObject['hidden'] == '0') {
            $this->dces[] = $dce;
        }
    }

    /**
     * Renders the container template, which includes all DCEs
     *
     * @return string
     */
    public function render()
    {
        $this->view->assign('dces', $this->dces);
        return $this->view->render();
    }

    /**
     * Get container color based on first container item.
     * If record is translated the color of the l18n_parent is returned.
     *
     * @return string Hex color, e.g. "#ff0066"
     */
    public function getContainerColor()
    {
        if (empty($this->dces)) {
            return '#fff';
        }

        /** @var Dce $firstDce */
        $firstDce = current($this->dces);
        $contentObject = $firstDce->getContentObject();
        if ($contentObject['sys_language_uid'] !== '0') {
            $originalRow = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                'uid = ' . $contentObject['l18n_parent']
            );
            if ($originalRow) {
                $contentObject = $originalRow;
            }
        }

        $colors = array_values(PageTS::get('tx_dce.defaults.simpleBackendView.containerGroupColors'));
        return $colors[$contentObject['uid'] % count($colors)];
    }
}
