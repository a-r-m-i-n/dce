<?php

namespace T3\Dce\Components\DceContainer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\TemplateRenderer\DceTemplateTypes;
use T3\Dce\Components\TemplateRenderer\StandaloneViewFactory;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\PageTS;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * The DCE Container.
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
     * Container constructor.
     */
    public function __construct(Dce $firstDceInContainer)
    {
        $this->firstDce = $firstDceInContainer;

        $viewFactory = GeneralUtility::makeInstance(StandaloneViewFactory::class);
        $this->view = $viewFactory->getDceTemplateView($firstDceInContainer, DceTemplateTypes::CONTAINER);
    }

    /**
     * Adds DceModel which contains the content element data.
     */
    public function addDce(Dce $dce): void
    {
        $this->dces[] = $dce;
    }

    /**
     * Renders the container template, which includes all DCEs.
     */
    public function render(): string
    {
        foreach ($this->dces as $dceInContainer) {
            if ($dceInContainer->isContainerDetailAutohide() && $dceInContainer->isDetailPageTriggered()) {
                $this->view->assign('dces', [$dceInContainer]);

                return $this->view->render();
            }
        }
        $this->view->assign('dces', $this->dces);

        return $this->view->render();
    }

    /**
     * Get container color based on first container item.
     * If record is translated the color of the l18n_parent is returned.
     *
     * @return string Hex color, e.g. "#ff0066"
     */
    public function getContainerColor(): string
    {
        if (empty($this->dces)) {
            return '#fff';
        }

        /** @var Dce $firstDce */
        $firstDce = current($this->dces);
        $contentObject = $firstDce->getContentObject();
        if ('0' !== $contentObject['sys_language_uid']) {
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
            $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
            $originalRow = $queryBuilder
                ->select('*')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($contentObject['l18n_parent'], \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetch();
            if ($originalRow) {
                $contentObject = $originalRow;
            }
        }

        $colors = array_values(PageTS::get('tx_dce.defaults.simpleBackendView.containerGroupColors'));

        return (string)$colors[$contentObject['uid'] % \count($colors)];
    }
}
