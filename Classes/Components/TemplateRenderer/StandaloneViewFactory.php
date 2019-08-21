<?php
namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * The Template Factory
 */
class StandaloneViewFactory implements SingletonInterface
{
    /**
     * @var array Cache for fluid instances
     */
    protected static $fluidTemplateCache = [];

    /**
     * Makes a new Fluid StandaloneView instance
     * with set DCE layout and partial root paths
     *
     * @return StandaloneView
     */
    public function makeNewDceView() : StandaloneView
    {
        /** @var StandaloneView $fluidTemplate */
        $fluidTemplate = GeneralUtility::makeInstance(StandaloneView::class);
        $fluidTemplate->setLayoutRootPaths([File::get('EXT:dce/Resources/Private/Layouts/')]);
        $fluidTemplate->setPartialRootPaths([File::get('EXT:dce/Resources/Private/Partials/')]);
        return $fluidTemplate;
    }

    /**
     * Creates new standalone view or returns cached one, if existing
     *
     * @param Dce $dce
     * @param int $templateType see class constants
     * @return StandaloneView
     */
    public function getDceTemplateView(Dce $dce, int $templateType) : StandaloneView
    {
        $cacheKey = $dce->getUid();
        if ($dce->getEnableContainer()) {
            $containerIterator = $dce->getContainerIterator();
            $cacheKey .= '-' . $containerIterator['index'];
        }
        if (isset(self::$fluidTemplateCache[$cacheKey][$templateType])) {
            return self::$fluidTemplateCache[$cacheKey][$templateType];
        }

        $view = $this->makeNewDceView();
        $this->applyDceTemplateTypeToView($view, $dce, $templateType);
        $this->setLayoutRootPaths($view, $dce);
        $this->setPartialRootPaths($view, $dce);

        $this->setAssignedVariables($view);
        if ($templateType !== DceTemplateTypes::CONTAINER) {
            $view->assign('dce', $dce);
        }

        self::$fluidTemplateCache[$cacheKey][$templateType] = $view;
        return $view;
    }

    /**
     * Applies the correct template (inline or file) to given StandaloneView instance.
     * The given templateType is respected.
     *
     * @param Dce $dce
     * @param int $templateType see class constants
     * @return void
     */
    protected function applyDceTemplateTypeToView(StandaloneView $view, Dce $dce, int $templateType) : void
    {
        $templateFields = DceTemplateTypes::$templateFields[$templateType];
        $typeGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['type']));

        if ($dce->$typeGetter() === 'inline') {
            $inlineTemplateGetter = 'get' . ucfirst(
                GeneralUtility::underscoredToLowerCamelCase($templateFields['inline'])
            );
            $view->setTemplateSource($dce->$inlineTemplateGetter() . ' ');
        } else {
            $fileTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['file']));
            $filePath = File::get($dce->$fileTemplateGetter());

            if (!file_exists($filePath)) {
                $view->setTemplateSource('');
            } else {
                $templateContent = file_get_contents($filePath);
                $view->setTemplateSource($templateContent . ' ');
            }
        }
    }

    /**
     * @param StandaloneView $view
     * @param Dce $dce
     */
    protected function setLayoutRootPaths(StandaloneView $view, Dce $dce) : void
    {
        $layoutRootPaths = $view->getLayoutRootPaths();
        if (!empty($dce->getTemplateLayoutRootPath())) {
            $layoutRootPaths[] = File::get($dce->getTemplateLayoutRootPath());
        }
        $view->setLayoutRootPaths($layoutRootPaths);
    }

    /**
     * @param StandaloneView $view
     * @param Dce $dce
     */
    protected function setPartialRootPaths(StandaloneView $view, Dce $dce) : void
    {
        $partialRootPaths = $view->getPartialRootPaths();
        if (!empty($dce->getTemplatePartialRootPath())) {
            $partialRootPaths[] = File::get($dce->getTemplatePartialRootPath());
        }
        $view->setPartialRootPaths($partialRootPaths);
    }

    /**
     * @param StandaloneView $view
     * @return void
     */
    protected function setAssignedVariables(StandaloneView $view) : void
    {
        if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
            $view->assign('TSFE', $GLOBALS['TSFE']);
            $view->assign('page', $GLOBALS['TSFE']->page);

            $typoScriptService = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Service\TypoScriptService');
            $view->assign(
                'tsSetup',
                $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup)
            );
        }
    }
}
