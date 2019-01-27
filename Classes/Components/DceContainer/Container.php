<?php
namespace T3\Dce\Components\DceContainer;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\PageTS;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Container
 * The DCE Container
 */
class Container
{
    /**
     * @var StandaloneView
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
    public function addDce(Dce $dce) : void
    {
        $contentObject = $dce->getContentObject();
        if ((int) $contentObject['hidden'] === 0) {
            $this->dces[] = $dce;
        }
    }

    /**
     * Renders the container template, which includes all DCEs
     *
     * @return string
     */
    public function render() : string
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
    public function getContainerColor() : string
    {
        if (empty($this->dces)) {
            return '#fff';
        }

        /** @var Dce $firstDce */
        $firstDce = current($this->dces);
        $contentObject = $firstDce->getContentObject();
        if ($contentObject['sys_language_uid'] !== '0') {
            $originalRow = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                'uid = ' . $contentObject['l18n_parent']
            );
            if ($originalRow) {
                $contentObject = $originalRow;
            }
        }

        $colors = array_values(PageTS::get('tx_dce.defaults.simpleBackendView.containerGroupColors'));
        return (string) $colors[$contentObject['uid'] % \count($colors)];
    }
}
