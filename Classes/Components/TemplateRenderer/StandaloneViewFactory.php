<?php

namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * The Template Factory.
 */
class StandaloneViewFactory implements SingletonInterface
{
    /**
     * @var array Cache for fluid instances
     */
    protected static $fluidTemplateCache = [];

    /**
     * Makes a new Fluid StandaloneView instance
     * with set DCE layout and partial root paths.
     */
    public function makeNewDceView(): StandaloneView
    {
        /** @var StandaloneView $fluidTemplate */
        $fluidTemplate = GeneralUtility::makeInstance(StandaloneView::class);
        $fluidTemplate->setLayoutRootPaths([File::get('EXT:dce/Resources/Private/Layouts/')]);
        $fluidTemplate->setPartialRootPaths([File::get('EXT:dce/Resources/Private/Partials/')]);

        return $fluidTemplate;
    }

    /**
     * Creates new standalone view or returns cached one, if existing.
     *
     * @param int $templateType see class constants
     */
    public function getDceTemplateView(Dce $dce, int $templateType): StandaloneView
    {
        $cacheKey = $dce->getUid();
        if ($dce->getEnableContainer()) {
            $containerIterator = $dce->getContainerIterator();
            if (null !== $containerIterator && isset($containerIterator['index'])) {
                $cacheKey .= '-' . $containerIterator['index'];
            }
        }
        if (isset(self::$fluidTemplateCache[$cacheKey][$templateType])) {
            return self::$fluidTemplateCache[$cacheKey][$templateType];
        }

        $view = $this->makeNewDceView();
        $this->applyDceTemplateTypeToView($view, $dce, $templateType);
        $this->setLayoutRootPaths($view, $dce);
        $this->setPartialRootPaths($view, $dce);

        $this->setAssignedVariables($view);
        if (DceTemplateTypes::CONTAINER !== $templateType) {
            $view->assign('dce', $dce);
        }

        self::$fluidTemplateCache[$cacheKey][$templateType] = $view;

        return $view;
    }

    /**
     * Applies the correct template (inline or file) to given StandaloneView instance.
     * The given templateType is respected.
     *
     * @param int $templateType see class constants
     */
    protected function applyDceTemplateTypeToView(StandaloneView $view, Dce $dce, int $templateType): void
    {
        $templateFields = DceTemplateTypes::$templateFields[$templateType];
        $typeGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['type']));

        if ('inline' === $dce->$typeGetter()) {
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

    protected function setLayoutRootPaths(StandaloneView $view, Dce $dce): void
    {
        $layoutRootPaths = $view->getLayoutRootPaths();
        if (!empty($dce->getTemplateLayoutRootPath())) {
            $layoutRootPaths[] = File::get($dce->getTemplateLayoutRootPath());
        }
        $view->setLayoutRootPaths($layoutRootPaths);
    }

    protected function setPartialRootPaths(StandaloneView $view, Dce $dce): void
    {
        $partialRootPaths = $view->getPartialRootPaths();
        if (!empty($dce->getTemplatePartialRootPath())) {
            $partialRootPaths[] = File::get($dce->getTemplatePartialRootPath());
        }
        $view->setPartialRootPaths($partialRootPaths);
    }

    protected function setAssignedVariables(StandaloneView $view): void
    {
        if (isset($GLOBALS['TSFE']) && Compatibility::isFrontendMode()) {
            $view->assign('TSFE', $GLOBALS['TSFE']);
            $view->assign('page', $GLOBALS['TSFE']->page);

            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $view->assign(
                'tsSetup',
                $typoScriptService->convertTypoScriptArrayToPlainArray($GLOBALS['TSFE']->tmpl->setup)
            );
        }
    }
}
