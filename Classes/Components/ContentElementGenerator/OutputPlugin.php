<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OutputPlugin.
 */
class OutputPlugin implements OutputInterface
{
    protected const CACHE_KEY = 'output_plugin';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(InputInterface $input, CacheManager $cacheManager)
    {
        $this->input = $input;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Injects plugin configuration
     * Call this in ext_localconf.php.
     */
    public function generate(): void
    {
        if (!$this->cacheManager->has(self::CACHE_KEY)) {
            $sourceCode = '';

            /** @var Typo3Version $versionInformation */
            $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
            $typicalPageContentGroupName = 'default';
            if ($versionInformation->getMajorVersion() < 13) {
                $typicalPageContentGroupName = 'common';
            }

            $sourceCode .= <<<PHP
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                    'mod.wizards.newContentElement.wizardItems.dce.header = LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tx_dce_domain_model_dce_long
                     mod.wizards.newContentElement.wizardItems.dce.after = $typicalPageContentGroupName'
                );

                PHP;
            foreach ($this->input->getDces() as $dce) {
                if ($dce['hidden']) {
                    continue;
                }
                $dceIdentifier = $dce['identifier'];
                $dceIdentifierSkipFirst4Chars = substr($dceIdentifier, 4);
                $dceCache = $dce['cache_dce'] ? '[]' : "[\T3\Dce\Controller\DceController::class => 'show']";
                $sourceCode .= <<<PHP
                    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                        'dce',
                        '$dceIdentifierSkipFirst4Chars',
                        [
                            \T3\Dce\Controller\DceController::class => 'show',
                        ],
                        $dceCache,
                        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
                    );

                    PHP;
                // When FSC/CSC is not installed
                if (!$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates']
                    || empty($GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'])
                ) {
                    $sourceCode .= <<<PHP
                            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
                                tt_content.$dceIdentifier = USER
                                tt_content.$dceIdentifier {
                                    userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                                    vendorName = T3
                                    extensionName = Dce
                                    pluginName = $dceIdentifierSkipFirst4Chars
                                }
                            ');

                        PHP;
                } else {
                    // When FSC is installed
                    if ($dce['direct_output'] || $dce['enable_container']) {
                        $sourceCode .= <<<PHP
                            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                                'dce',
                                'setup',
                                'temp.dceContentElement < tt_content.$dceIdentifier.20
                                 tt_content.$dceIdentifier >
                                 tt_content.$dceIdentifier < temp.dceContentElement
                                 temp.dceContentElement >
                                ',
                                43
                            );

                            PHP;
                    }
                }

                $sourceCode .= <<<PHP
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                        'dce',
                        'setup',
                        "# Hide lib.stdheader for DCE with identifier $dceIdentifier
                         tt_content.$dceIdentifier.10 >",
                        43
                    );

                    PHP;

                if ($dce['wizard_enable']) {
                    $iconIdentifierCode = $dce['hasCustomWizardIcon']
                        ? 'ext-dce-' . $dceIdentifier . '-customwizardicon'
                        : $dce['wizard_icon'];

                    $wizardCategory = $dce['wizard_category'] ?? '';
                    $flexformLabel = $dce['flexform_label'] ?? '';
                    $title = addcslashes($dce['title'] ?? '', "'\"");
                    $description = addcslashes($dce['wizard_description'] ?? '', "'\"");

                    $sourceCode .= <<<PHP
                        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                            "
                            mod.wizards.newContentElement.wizardItems.$wizardCategory.elements.$dceIdentifier {
                                iconIdentifier = $iconIdentifierCode
                                title = $title
                                description = $description
                                tt_content_defValues {
                                    CType = $dceIdentifier
                                }
                            }
                            mod.wizards.newContentElement.wizardItems.$wizardCategory.show := addToList($dceIdentifier)
                            TCEFORM.tt_content.pi_flexform.types.$dceIdentifier.label = $flexformLabel
                            "
                        );

                        PHP;
                }
            }
            $this->cacheManager->set(self::CACHE_KEY, $sourceCode);
        }
        $this->cacheManager->requireOnce(self::CACHE_KEY);
    }
}
