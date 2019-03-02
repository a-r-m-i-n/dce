<?php
namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class OutputPlugin
 */
class OutputPlugin implements OutputInterface
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Injects plugin configuration
     * Call this in ext_localconf.php
     *
     * @return void
     */
    public function generate() : void
    {
        ExtensionManagementUtility::addPageTSConfig(
            'mod.wizards.newContentElement.wizardItems.dce.header = ' .
            'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce_long'
        );

        foreach ($this->input->getDces() as $dce) {
            if ($dce['hidden']) {
                continue;
            }
            $dceIdentifier = $dce['identifier'];

            ExtensionUtility::configurePlugin(
                'T3.dce',
                substr($dceIdentifier, 4),
                [
                    'Dce' => 'show',
                ],
                $dce['cache_dce'] ? [] : ['Dce' => 'show'],
                ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
            );

            if ($dce['direct_output']) {
                ExtensionManagementUtility::addTypoScript(
                    'dce',
                    'setup',
                    <<<TYPOSCRIPT
temp.dceContentElement < tt_content.$dceIdentifier.20
tt_content.$dceIdentifier >
tt_content.$dceIdentifier < temp.dceContentElement
temp.dceContentElement >
TYPOSCRIPT
                    ,
                    43
                );
            }

            ExtensionManagementUtility::addTypoScript(
                'dce',
                'setup',
                "# Hide lib.stdheader for DCE with identifier $dceIdentifier
                 tt_content.$dceIdentifier.10 >",
                43
            );

            if ($dce['hide_default_ce_wrap'] && ExtensionManagementUtility::isLoaded('css_styled_content')) {
                ExtensionManagementUtility::addTypoScript(
                    'dce',
                    'setup',
                    "# Hide default wrapping for content elements for DCE with identifier $dceIdentifier}
                     tt_content.stdWrap.innerWrap.cObject.default.stdWrap.if.value := addToList($dceIdentifier)",
                    43
                );
            }

            if ($dce['enable_container'] && ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
                ExtensionManagementUtility::addTypoScript(
                    'dce',
                    'setup',
                    "# Change fluid_styled_content template name for DCE with identifier $dceIdentifier
                     tt_content.$dceIdentifier.templateName = DceContainerElement",
                    43
                );
            }

            if ($dce['wizard_enable']) {
                if ($dce['hasCustomWizardIcon'] && !empty($dce['wizard_custom_icon'])) {
                    $iconRegistry = GeneralUtility::makeInstance(
                        IconRegistry::class
                    );
                    $iconRegistry->registerIcon(
                        "ext-dce-$dceIdentifier-customwizardicon",
                        BitmapIconProvider::class,
                        ['source' => $dce['wizard_custom_icon']]
                    );
                }

                $iconIdentifierCode = $dce['hasCustomWizardIcon'] ? "ext-dce-$dceIdentifier-customwizardicon"
                    : $dce['wizard_icon'];

                $wizardCategory = $dce['wizard_category'];
                $flexformLabel = $dce['flexform_label'];
                $title = addcslashes($dce['title'], "'");
                $description = addcslashes($dce['wizard_description'], "'");

                ExtensionManagementUtility::addPageTSConfig(
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
            }
        }
    }
}
