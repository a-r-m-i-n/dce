<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function generate(): void
    {
        if (!$this->cacheManager->has(self::CACHE_KEY)) {
            $sourceCode = '';

            $sourceCode .= <<<PHP
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                    'mod.wizards.newContentElement.wizardItems.dce.header = ' .
                    'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tx_dce_domain_model_dce_long'
                );

                PHP;
            foreach ($this->input->getDces() as $dce) {
                if ($dce['hidden']) {
                    continue;
                }
                $dceIdentifier = $dce['identifier'];
                $dceIdentifierSkipFirst4Chars = substr($dceIdentifier, 4);
                $dceCache = $dce['cache_dce'] ? '[]' : "['Dce' => 'show']";
                $sourceCode .= <<<PHP
                    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                        \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? 'dce' : 'T3.dce',
                        '$dceIdentifierSkipFirst4Chars',
                        [
                            \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? \T3\Dce\Controller\DceController::class : 'Dce' => 'show',
                        ],
                        $dceCache,
                        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
                    );

                    PHP;
                // When FSC/CSC is not installed
                if (!$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'] ||
                    empty($GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'])
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
                    if ($dce['direct_output']) {
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

                if ($dce['hide_default_ce_wrap'] && ExtensionManagementUtility::isLoaded('css_styled_content')) {
                    $sourceCode .= <<<PHP
                        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                            'dce',
                            'setup',
                            "# Hide default wrapping for content elements for DCE with identifier $dceIdentifier}
                             tt_content.stdWrap.innerWrap.cObject.default.stdWrap.if.value := addToList($dceIdentifier)",
                            43
                        );

                        PHP;
                }

                if ($dce['enable_container'] && ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
                    $sourceCode .= <<<PHP
                        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                            'dce',
                            'setup',
                            "# Change fluid_styled_content template name for DCE with identifier $dceIdentifier
                             tt_content.$dceIdentifier.templateName = DceContainerElement",
                            43
                        );

                        PHP;
                }

                if ($dce['wizard_enable']) {
                    if ($dce['hasCustomWizardIcon'] && !empty($dce['wizard_custom_icon'])) {
                        $wizardCustomIcon = $dce['wizard_custom_icon'];
                        $sourceCode .= <<<PHP
                            \$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                                \TYPO3\CMS\Core\Imaging\IconRegistry::class
                            );
                            \$iconRegistry->registerIcon(
                                "ext-dce-$dceIdentifier-customwizardicon",
                                \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
                                ['source' => '$wizardCustomIcon']
                            );

                            PHP;
                    }

                    $iconIdentifierCode = $dce['hasCustomWizardIcon'] ? "ext-dce-$dceIdentifier-customwizardicon"
                        : $dce['wizard_icon'];

                    $wizardCategory = $dce['wizard_category'];
                    $flexformLabel = $dce['flexform_label'];
                    $title = addcslashes($dce['title'], "'\"");
                    $description = addcslashes($dce['wizard_description'], "'\"");

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
