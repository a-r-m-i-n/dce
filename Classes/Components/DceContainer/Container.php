<?php
namespace ArminVieweg\Dce\Components\DceContainer;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
    protected $dces = array();

    /**
     * Container constructor
     *
     * @param Dce $firstDceInContainer
     * @return Container
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
     * @return mixed
     */
    public function getContainerColor()
    {
        /** @var Dce $firstDce */
        $firstDce = current($this->dces);
        $contentObject = $firstDce->getContentObject();

        $colors = array_values(PageTS::get('tx_dce.defaults.simpleBackendView.containerGroupColors'));
        return $colors[$contentObject['uid'] % count($colors)];
    }
}
