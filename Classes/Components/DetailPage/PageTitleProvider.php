<?php

declare(strict_types = 1);

namespace T3\Dce\Components\DetailPage;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Model\Dce;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\PageTitle\RecordPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PageTitleProvider extends AbstractPageTitleProvider
{
    private static ?array $typoScriptSettings = null;

    public function __construct()
    {
        if (method_exists($this, 'setRequest')) {
            $this->setRequest($GLOBALS['TYPO3_REQUEST']);
        }
    }

    public function generate(Dce $dce): void
    {
        $method = $dce->getDetailpageUseSlugAsTitle() . 'Title';
        if (method_exists($this, $method)) {
            $this->$method($dce);
        }
    }

    protected function overwriteTitle(Dce $dce): void
    {
        $this->title = $this->buildDceTitle($dce);
    }

    protected function prependTitle(Dce $dce): void
    {
        /** @var RecordPageTitleProvider $pageTitle */
        $pageTitle = GeneralUtility::makeInstance(RecordPageTitleProvider::class);
        if (method_exists($pageTitle, 'setRequest')) {
            $pageTitle->setRequest($this->request);
        }
        $originalPageTitle = $pageTitle->getTitle();

        $dceTitle = $this->buildDceTitle($dce);
        $settings = $this->getTypoScriptSettings();
        $dceTitleWithWrap = $this->wrapDceTitle($dceTitle, $settings['prependWrap'] ?? '');

        $this->title = $dceTitleWithWrap . $originalPageTitle;
    }

    protected function appendTitle(Dce $dce): void
    {
        /** @var RecordPageTitleProvider $pageTitle */
        $pageTitle = GeneralUtility::makeInstance(RecordPageTitleProvider::class);
        if (method_exists($pageTitle, 'setRequest')) {
            $pageTitle->setRequest($this->request);
        }
        $originalPageTitle = $pageTitle->getTitle();

        $dceTitle = $this->buildDceTitle($dce);
        $settings = $this->getTypoScriptSettings();
        $dceTitleWithWrap = $this->wrapDceTitle($dceTitle, $settings['appendWrap'] ?? '');

        $this->title = $originalPageTitle . $dceTitleWithWrap;
    }

    private function buildDceTitle(Dce $dce): ?string
    {
        try {
            return SlugGenerator::getSlugFromDce(
                $dce,
                $dce->getDetailpageTitleExpression()
            );
        } catch (\Exception $e) {
        }

        return '';
    }

    private function getTypoScriptSettings(): array
    {
        if (null === self::$typoScriptSettings) {
            $configManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $ts = $configManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            self::$typoScriptSettings = $ts['config.']['pageTitleProviders.']['dce.'] ?? [];
        }

        return self::$typoScriptSettings;
    }

    private function wrapDceTitle(string $dceTitle, string $noTrimWrapSetting): string
    {
        /** @var ContentObjectRenderer $cObj */
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        return $cObj->stdWrap($dceTitle, ['noTrimWrap' => $noTrimWrapSetting]);
    }
}
