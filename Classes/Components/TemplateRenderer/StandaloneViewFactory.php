<?php

namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\TypoScript;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\StandaloneView;

class StandaloneViewFactory implements SingletonInterface
{
    protected static array $fluidTemplateCache = [];

    public function __construct(private readonly TypoScript $typoScriptUtility)
    {
    }

    /**
     * Makes a new Fluid StandaloneView instance
     * with set DCE layout and partial root paths.
     */
    public function makeNewDceView(): StandaloneView
    {
        /** @var StandaloneView $fluidTemplate */
        $fluidTemplate = GeneralUtility::makeInstance(StandaloneView::class);

        $renderingContext = $fluidTemplate->getRenderingContext();
        if (isset($GLOBALS['TYPO3_REQUEST'])
            && $renderingContext instanceof RenderingContext
            && null === $renderingContext->getRequest()
        ) {
            $renderingContext->setRequest($GLOBALS['TYPO3_REQUEST']);
        }

        $viewPaths = $this->getTyposcriptViewPaths();
        $fluidTemplate->setLayoutRootPaths($this->resolvePaths($viewPaths['layoutRootPaths']));
        $fluidTemplate->setTemplateRootPaths($this->resolvePaths($viewPaths['templateRootPaths']));
        $fluidTemplate->setPartialRootPaths($this->resolvePaths($viewPaths['partialRootPaths']));

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
            $inlineTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['inline']));
            $view->setTemplateSource($dce->$inlineTemplateGetter() . ' ');
        } else {
            $fileTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['file']));
            $templateName = $dce->$fileTemplateGetter();

            // try to render using typoscript files paths
            $view->setTemplate($templateName);

            // if the file does not exists, try using fullpath
            if (!$view->hasTemplate()) {
                $filePath = GeneralUtility::getFileAbsFileName($dce->$fileTemplateGetter());

                if (!file_exists($filePath)) {
                    $view->setTemplateSource('');
                } else {
                    $templateContent = file_get_contents($filePath);
                    $view->setTemplateSource($templateContent . ' ');
                }
            }
        }
    }

    protected function setLayoutRootPaths(StandaloneView $view, Dce $dce): void
    {
        $layoutRootPaths = $view->getLayoutRootPaths();
        if (!empty($dce->getTemplateLayoutRootPath())) {
            $layoutRootPaths[] = GeneralUtility::getFileAbsFileName($dce->getTemplateLayoutRootPath());
        }
        $view->setLayoutRootPaths($layoutRootPaths);
    }

    protected function setPartialRootPaths(StandaloneView $view, Dce $dce): void
    {
        $partialRootPaths = $view->getPartialRootPaths();
        if (!empty($dce->getTemplatePartialRootPath())) {
            $partialRootPaths[] = GeneralUtility::getFileAbsFileName($dce->getTemplatePartialRootPath());
        }
        $view->setPartialRootPaths($partialRootPaths);
    }

    protected function setAssignedVariables(StandaloneView $view): void
    {
        if (isset($GLOBALS['TYPO3_REQUEST']) && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            // TODO: $GLOBALS['TSFE'] is deprecated and will get removed in TYPO3 v13
            $view->assign('TSFE', $GLOBALS['TSFE']);
            $view->assign('page', $GLOBALS['TSFE']->page);

            $view->assign('tsSetup', $this->typoScriptUtility->getTypoScriptSetupArray());
        }
    }

    /**
     * Returns the typoscript configuration for path : plugin.tx_dce.view.
     */
    protected function getTyposcriptViewPaths(): array
    {
        // default views settings because TSFE is null when creating a new dce
        $viewsPaths = [
            'layoutRootPaths' => [0 => 'EXT:dce/Resources/Private/Layouts/'],
            'templateRootPaths' => [0 => 'EXT:dce/Resources/Private/Templates/'],
            'partialRootPaths' => [0 => 'EXT:dce/Resources/Private/Partials/'],
        ];

        $typoScriptSetup = $this->typoScriptUtility->getTypoScriptSetupArray();
        if ($typoScriptSetup && isset($typoScriptSetup['plugin']['tx_dce']['view'])) {
            $viewsPaths = $typoScriptSetup['plugin']['tx_dce']['view'];
        }

        return $viewsPaths;
    }

    /**
     * Resolve file paths for entire array.
     */
    protected function resolvePaths(array $paths): array
    {
        return array_map(static function ($path) {
            return GeneralUtility::getFileAbsFileName($path);
        }, $paths);
    }
}
