<?php
namespace ArminVieweg\Dce;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * DCE Injector
 * Injects code (configuration) for configured DCEs dynamically
 */
class Injector
{
    /**
     * Injects TCA
     * Call this in Configuration/TCA/Overrides/tt_content.php
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function injectTca()
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            0 => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce_long',
            1 => '--div--'
        ];

        $fieldRowsWithNewColumns = Components\FlexformToTcaMapper\Mapper::getDceFieldRowsWithNewTcaColumns();
        if (\count($fieldRowsWithNewColumns) > 0) {
            $newColumns = [];
            foreach ($fieldRowsWithNewColumns as $fieldRow) {
                $newColumns[$fieldRow['new_tca_field_name']] = ['label' => '', 'config' => ['type' => 'passthrough']];
            }
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $newColumns);
        }

        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            0 => 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce.miscellaneous',
            1 => '--div--'
        ];

        foreach ($this->getDatabaseDces() as $dce) {
            if ($dce['hidden']) {
                continue;
            }

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    addcslashes($dce['title'], "'"),
                    'dce_dceuid' . $dce['uid'],
                    $dce['hasCustomWizardIcon']
                        ? 'ext-dce-dceuid' . $dce['uid'] . '-customwizardicon'
                        : $dce['wizard_icon'],
                ]
            );

            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['dce_dceuid' . $dce['uid']] =
                $dce['hasCustomWizardIcon']
                    ? 'ext-dce-dceuid' . $dce['uid'] . '-customwizardicon'
                    : $dce['wizard_icon'];

            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['dce_dceuid' . $dce['uid']] =
                'pi_flexform';
            $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][',dce_dceuid' . $dce['uid']] =
                $this->renderFlexformXml($dce);

            $showAccessTabCode = $dce['show_access_tab']
                ? '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                  --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,'
                : '';
            $showMediaTabCode = $dce['show_media_tab']
                ? '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,assets,'
                : '';
            $showCategoryTabCode = $dce['show_category_tab']
                ? '--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,' .
                  'categories,'
                : '';

            $GLOBALS['TCA']['tt_content']['types']['dce_dceuid' . $dce['uid'] . '']['showitem'] =
                '--palette--;;dce_palette_' . $dce['uid'] . '_head,
                --palette--;;dce_palette_' . $dce['uid'] . ',
                pi_flexform,' . $showAccessTabCode . $showMediaTabCode . $showCategoryTabCode . '
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:tabs.extended';

            $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '_head']['canNotCollapse'] = true;

            $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '_head']['showitem'] =
                'CType' . ($dce['enable_container'] ? ',tx_dce_new_container' : '');

            if ($dce['palette_fields']) {
                $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '']['canNotCollapse'] = true;
                $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '']['showitem']
                    = $dce['palette_fields'];

                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')) {
                    $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '']['showitem'] .=
                        ',tx_gridelements_container,tx_gridelements_columns';
                }
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('flux')) {
                    $GLOBALS['TCA']['tt_content']['palettes']['dce_palette_' . $dce['uid'] . '']['showitem'] .=
                        ',tx_flux_column,tx_flux_parent';
                }
            }
        }
    }

    /**
     * Injects plugin configuration
     * Call this in ext_localconf.php
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function injectPluginConfiguration()
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'mod.wizards.newContentElement.wizardItems.dce.header = ' .
            'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:tx_dce_domain_model_dce_long'
        );

        foreach ($this->getDatabaseDces() as $dce) {
            if ($dce['hidden']) {
                continue;
            }

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'ArminVieweg.dce',
                'dceuid' . $dce['uid'],
                [
                    'Dce' => 'show',
                ],
                $dce['cache_dce'] ? [] : ['Dce' => 'show'],
                \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
            );

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                'dce',
                'setup',
                '# Hide lib.stdheader for DCE with uid ' . $dce['uid'] . '
            tt_content.dce_dceuid' . $dce['uid'] . '.10 >',
                43
            );

            if ($dce['hide_default_ce_wrap'] &&
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('css_styled_content')
            ) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                    'dce',
                    'setup',
                    '# Hide default wrapping for content elements for DCE with uid ' . $dce['uid'] . '
                tt_content.stdWrap.innerWrap.cObject.default.stdWrap.if.value := addToList(dce_dceuid' . $dce['uid'] .
                    ')',
                    43
                );
            }

            if ($dce['enable_container'] && ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                    'dce',
                    'setup',
                    '# Change fluid_styled_content template name for DCE with uid ' . $dce['uid'] . '
                     tt_content.dce_dceuid' . $dce['uid'] . '.templateName = DceContainerElement',
                    43
                );
            }

            if ($dce['wizard_enable']) {
                if ($dce['hasCustomWizardIcon'] && !empty($dce['wizard_custom_icon'])) {
                    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                        \TYPO3\CMS\Core\Imaging\IconRegistry::class
                    );
                    $iconRegistry->registerIcon(
                        'ext-dce-dceuid' . $dce['uid'] . '-customwizardicon',
                        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
                        ['source' => $dce['wizard_custom_icon']]
                    );
                }

                $iconIdentifierCode = $dce['hasCustomWizardIcon'] ? 'ext-dce-dceuid' . $dce['uid'] .
                    '-customwizardicon' : $dce['wizard_icon'];

                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                    'mod.wizards.newContentElement.wizardItems.' . $dce['wizard_category'] . '.elements.dce_dceuid' .
                    $dce['uid'] . ' {
                    iconIdentifier = ' . $iconIdentifierCode . '
                    title = ' . addcslashes($dce['title'], "'") . '
                    description = ' . addcslashes($dce['wizard_description'], "'") . '
                    tt_content_defValues {
                        CType = dce_dceuid' . $dce['uid'] . '
                    }
                }
                mod.wizards.newContentElement.wizardItems.' . $dce['wizard_category'] . '.show := addToList(dce_dceuid'
                    . $dce['uid'] . ')
                TCEFORM.tt_content.pi_flexform.types.dce_dceuid' . $dce['uid'] . '.label = ' . $dce['flexform_label']
                );
            }
        }
    }

    /**
     * Renders Flexform XML for given DCE
     * using Fluid template engine
     *
     * @param array $singleDceArray
     * @return string
     */
    public function renderFlexformXml(array $singleDceArray)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $fluidTemplate */
        $fluidTemplate = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $fluidTemplate->setLayoutRootPaths([Utility\File::get('EXT:dce/Resources/Private/Layouts/')]);
        $fluidTemplate->setPartialRootPaths([Utility\File::get('EXT:dce/Resources/Private/Partials/')]);
        $fluidTemplate->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/FlexFormsXML.html'
        );
        $fluidTemplate->assign('dce', $singleDceArray);
        return $fluidTemplate->render();
    }

    /**
     * Returns all available DCE as array with this format
     * (just most important fields listed):
     *
     * DCE
     *    |_ uid
     *    |_ title
     *    |_ tabs <array>
     *    |    |_ title
     *    |    |_ fields <array>
     *    |        |_ uid
     *    |        |_ title
     *    |        |_ variable
     *    |        |_ configuration
     *    |_ ...
     *
     * @return array with DCE -> containing tabs -> containing fields
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseDces()
    {
        /** @var $databaseConnection \ArminVieweg\Dce\Utility\DatabaseConnection */
        $databaseConnection = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();

        $tables = $databaseConnection->admin_get_tables();
        if (!\in_array('tx_dce_domain_model_dce', $tables) || !\in_array('tx_dce_domain_model_dcefield', $tables)) {
            return [];
        }

        $res = $databaseConnection->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dce',
            'deleted=0 AND pid=0',
            '',
            'sorting asc'
        );

        $dces = [];
        foreach ($res as $row) {
            $res2 = $databaseConnection->exec_SELECTgetRows(
                '*',
                'tx_dce_domain_model_dcefield',
                'parent_dce = ' . $row['uid'] . ' AND deleted=0 AND hidden=0',
                '',
                'sorting asc'
            );

            $tabs = [
                0 => [
                'title' => 'LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab',
                'variable' => 'tabGeneral',
                'fields' => []
                ]
            ];
            $index = 0;
            foreach ($res2 as $row2) {
                if ($row2['type'] === '1') {
                    // Create new Tab
                    $index++;
                    $tabs[$index] = [];
                    $tabs[$index]['title'] = $row2['title'];
                    $tabs[$index]['variable'] = $row2['variable'];
                    $tabs[$index]['fields'] = [];
                    continue;
                } elseif ($row2['type'] === '2') {
                    $res3 = $databaseConnection->exec_SELECTgetRows(
                        '*',
                        'tx_dce_domain_model_dcefield',
                        'parent_field = ' . $row2['uid'] . ' AND deleted=0 AND hidden=0',
                        '',
                        'sorting asc'
                    );

                    $sectionFields = [];
                    foreach ($res3 as $row3) {
                        if ($row3['type'] === '0') {
                            // add fields of section to fields
                            $sectionFields[] = $row3;
                        }
                    }
                    $row2['section_fields'] = $sectionFields;
                    $tabs[$index]['fields'][] = $row2;
                } else {
                    // usual element
                    $tabs[$index]['fields'][] = $row2;
                }
            }
            if (\count($tabs[0]['fields']) === 0) {
                unset($tabs[0]);
            }

            $row['tabs'] = $tabs;
            $row['hasCustomWizardIcon'] = ($row['wizard_icon'] === 'custom') ? true : false;
            $dces[] = $row;
        }

        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $dces = $this->ensureGridelementsFieldCompatibility($dces);
        }
        return $dces;
    }

    /**
     * Iterates through given DCE rows and add field "" to DCE palettes
     * if not already set.
     *
     * @param array $dces
     * @return array
     */
    protected function ensureGridelementsFieldCompatibility($dces)
    {
        foreach ($dces as $key => $dceRow) {
            $paletteFields = GeneralUtility::trimExplode(',', $dceRow['palette_fields'], true);
            if (!\in_array('colPos', $paletteFields)) {
                $paletteFields[] = 'colPos';
            }
            $dces[$key]['palette_fields'] = implode(', ', $paletteFields);
        }
        return $dces;
    }
}
