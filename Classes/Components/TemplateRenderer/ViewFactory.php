<?php

namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use Psr\Http\Message\ServerRequestInterface;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\TypoScript;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;

class ViewFactory implements SingletonInterface
{
    protected static array $fluidTemplateCache = [];

    public function __construct(
        private readonly ViewFactoryInterface $viewFactory,
        private readonly TypoScript $typoScriptUtility
    ) {
    }

    /**
     * Makes a new Fluid view instance
     * with set DCE layout and partial root paths.
     */
    public function makeNewDceView(?ServerRequestInterface $request = null): ViewInterface|FluidViewAdapter
    {
        $viewPaths = $this->getTyposcriptViewPaths($request);
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: $this->resolvePaths($viewPaths['templateRootPaths']),
            partialRootPaths: $this->resolvePaths($viewPaths['partialRootPaths']),
            layoutRootPaths: $this->resolvePaths($viewPaths['layoutRootPaths']),
            request: $request,
        );

        return $this->viewFactory->create($viewFactoryData);
    }

    /**
     * Creates new standalone view or returns cached one, if existing.
     */
    public function getDceTemplateView(Dce $dce, DceTemplateTypes $templateType, ?ServerRequestInterface $request = null): ViewInterface|FluidViewAdapter
    {
        $cacheKey = $dce->getUid();
        if ($dce->getEnableContainer()) {
            $containerIterator = $dce->getContainerIterator();
            if (null !== $containerIterator && isset($containerIterator['index'])) {
                $cacheKey .= '-' . $containerIterator['index'];
            }
        }
        if (isset(self::$fluidTemplateCache[$cacheKey][$templateType->value])) {
            return self::$fluidTemplateCache[$cacheKey][$templateType->value];
        }

        $view = $this->makeNewDceView($request);
        if (!$view instanceof FluidViewAdapter) {
            return $view;
        }

        $this->applyDceTemplateTypeToView($view, $dce, $templateType);
        $this->setLayoutRootPaths($view, $dce);
        $this->setPartialRootPaths($view, $dce);

        $this->setAssignedVariables($view, $request);
        if (DceTemplateTypes::CONTAINER !== $templateType) {
            $view->assign('dce', $dce);
        }

        self::$fluidTemplateCache[$cacheKey][$templateType->value] = $view;

        return $view;
    }

    /**
     * Applies the correct template (inline or file) to given StandaloneView instance.
     * The given templateType is respected.
     */
    protected function applyDceTemplateTypeToView(FluidViewAdapter $view, Dce $dce, DceTemplateTypes $templateType): void
    {
        $templateFields = $templateType->getTemplateFields();
        $typeGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['type']));

        if ('inline' === $dce->$typeGetter()) {
            $inlineTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['inline']));
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($dce->$inlineTemplateGetter() . ' ');
        } else {
            $fileTemplateGetter = 'get' . ucfirst(GeneralUtility::underscoredToLowerCamelCase($templateFields['file']));
            $filePath = GeneralUtility::getFileAbsFileName($dce->$fileTemplateGetter());

            if (!file_exists($filePath)) {
                $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('');
            } else {
                $templateContent = file_get_contents($filePath);
                $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateContent . ' ');
            }
        }
    }

    protected function setLayoutRootPaths(FluidViewAdapter $view, Dce $dce): void
    {
        $layoutRootPaths = $view->getRenderingContext()->getTemplatePaths()->getLayoutRootPaths();
        if (!empty($dce->getTemplateLayoutRootPath())) {
            $layoutRootPaths[] = GeneralUtility::getFileAbsFileName($dce->getTemplateLayoutRootPath());
        }
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths($layoutRootPaths);
    }

    protected function setPartialRootPaths(FluidViewAdapter $view, Dce $dce): void
    {
        $partialRootPaths = $view->getRenderingContext()->getTemplatePaths()->getPartialRootPaths();
        if (!empty($dce->getTemplatePartialRootPath())) {
            $partialRootPaths[] = GeneralUtility::getFileAbsFileName($dce->getTemplatePartialRootPath());
        }
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths($partialRootPaths);
    }

    protected function setAssignedVariables(FluidViewAdapter $view, ?ServerRequestInterface $request): void
    {
        if (null !== $request && ApplicationType::fromRequest($request)->isFrontend()) {
            $pageInformation = $request->getAttribute('frontend.page.information');
            $view->assign('page', $pageInformation->getPageRecord());
            $view->assign('pageInformation', $pageInformation);

            if ($typoScriptSetupArray = $this->typoScriptUtility->getTypoScriptSetupArray($request)) {
                $view->assign('tsSetup', $typoScriptSetupArray);
            }

            if ($site = $request->getAttribute('site')) {
                $view->assign('site', $site);
            }
        }
    }

    /**
     * Returns the typoscript configuration for path : plugin.tx_dce.view.
     */
    protected function getTyposcriptViewPaths(?ServerRequestInterface $request): array
    {
        // Default paths are also needed when creating a DCE outside a frontend request.
        $viewsPaths = [
            'layoutRootPaths' => [0 => 'EXT:dce/Resources/Private/Layouts/'],
            'templateRootPaths' => [0 => 'EXT:dce/Resources/Private/Templates/'],
            'partialRootPaths' => [0 => 'EXT:dce/Resources/Private/Partials/'],
        ];

        $typoScriptSetup = $this->typoScriptUtility->getTypoScriptSetupArray($request);
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
