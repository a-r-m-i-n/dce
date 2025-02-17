<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OutputTcaAndFlexForm.
 */
class OutputTcaAndFlexForm
{
    protected const CACHE_KEY = 'output_tca_and_flexform';

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
     * Injects TCA
     * Call this in Configuration/TCA/Overrides/tt_content.php.
     */
    public function generate(): void
    {
        if (!$this->cacheManager->has(self::CACHE_KEY)) {
            $sourceCode = '';

            $sourceCode .= <<<PHP
                    \$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups']['dce'] =
                        'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tx_dce_domain_model_dce_long';
                PHP;

            $fieldRowsWithNewColumns = Mapper::getDceFieldRowsWithNewTcaColumns();
            if (\count($fieldRowsWithNewColumns) > 0) {
                $newColumns = [];
                foreach ($fieldRowsWithNewColumns as $fieldRow) {
                    $newColumns[$fieldRow['new_tca_field_name']] = [
                        'label' => '',
                        'config' => ['type' => 'passthrough'],
                    ];
                }
                $newColumnsAsCode = var_export($newColumns, true);
                $sourceCode .= <<<PHP
                        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $newColumnsAsCode);

                    PHP;
            }

            $sourceCode .= <<<PHP
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup(
                    'tt_content',
                    'CType',
                    'dce',
                    'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tx_dce_domain_model_dce.miscellaneous'
                );

                PHP;

            foreach ($this->input->getDces() as $dce) {
                $sourceCode .= $this->generateTcaForDces($dce) . PHP_EOL;
            }

            $this->cacheManager->set(self::CACHE_KEY, $sourceCode);
        }
        $this->cacheManager->requireOnce(self::CACHE_KEY);
    }

    /**
     * Generates TCA for single DCE.
     *
     * @param array $dce DCE row
     *
     * @return string Source code
     */
    protected function generateTcaForDces(array $dce): string
    {
        if ($dce['hidden'] || $dce['deleted']) {
            return '';
        }
        $sourceCode = '';
        $dceIdentifier = $dce['identifier'];

        $dceTitle = addcslashes($dce['title'], "'");
        $dceIcon = $dce['hasCustomWizardIcon'] ? 'ext-dce-' . $dceIdentifier . '-customwizardicon' : $dce['wizard_icon'];

        $sourceCode .= <<<PHP
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    'label' => '$dceTitle',
                    'value' => '$dceIdentifier',
                    'icon' => '$dceIcon',
                    'group' => 'dce'
                ]
            );

            \$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['$dceIdentifier'] = '$dceIcon';

            PHP;

        $flexformString = $this->renderFlexformXml($dce);
        $sourceCode .= <<<PHP
            \$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['$dceIdentifier'] = 'pi_flexform';
            \$GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*,$dceIdentifier'] = <<<XML
            $flexformString
            XML;

            PHP;

        $showAccessTabCode = $dce['show_access_tab']
            ? '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
              --palette--;;hidden,
              --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,'
            : '';
        $showMediaTabCode = $dce['show_media_tab']
            ? '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,assets,' : '';
        $showCategoryTabCode = $dce['show_category_tab']
            ? '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,categories,' : '';

        $paletteIdentifier = 'dce_palette_' . $dceIdentifier;
        $showItem = <<<TEXT
            --palette--;;{$paletteIdentifier}_head,
            --palette--;;$paletteIdentifier,
            pi_flexform,$showAccessTabCode$showMediaTabCode$showCategoryTabCode
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
            TEXT;

        $paletteIdentifierHead = $paletteIdentifier . '_head';
        $dceCType = 'CType' . ($dce['enable_container'] ? ',tx_dce_new_container' : '');

        $sourceCode .= <<<PHP
            \$GLOBALS['TCA']['tt_content']['palettes']['$paletteIdentifierHead']['showitem'] = '$dceCType';
            \$GLOBALS['TCA']['tt_content']['types']['$dceIdentifier']['showitem'] = '$showItem';
            \$GLOBALS['TCA']['tt_content']['types']['$dceIdentifier']['previewRenderer'] =
                \T3\Dce\Components\BackendView\DcePreviewRenderer::class;

            PHP;

        if ($dce['palette_fields']) {
            $paletteFields = $dce['palette_fields'];
            // remove access-fields from dce_palette, if Access Tab should be shown
            if (!empty($showAccessTabCode)) {
                $fieldsToRemove = ['hidden', 'starttime', 'endtime', 'fe_group'];
                $paletteFields = GeneralUtility::trimExplode(',', $paletteFields, true);
                $paletteFields = implode(',', array_diff($paletteFields, $fieldsToRemove));
            }
            $paletteFields = str_replace(
                ['--linebreak1--', '--linebreak2--', '--linebreak3--'],
                '--linebreak--',
                $paletteFields
            );

            $sourceCode .= <<<PHP
                \$GLOBALS['TCA']['tt_content']['palettes']['$paletteIdentifier']['showitem'] = '$paletteFields';

                PHP;

            if (ExtensionManagementUtility::isLoaded('container')) {
                $sourceCode .= <<<PHP
                    \$GLOBALS['TCA']['tt_content']['palettes']['$paletteIdentifier']['showitem'] .=
                        ',tx_container_parent';

                    PHP;
            }
            if (ExtensionManagementUtility::isLoaded('gridelements')) {
                $sourceCode .= <<<PHP
                    \$GLOBALS['TCA']['tt_content']['palettes']['$paletteIdentifier']['showitem'] .=
                        ',tx_gridelements_container,tx_gridelements_columns';

                    PHP;
            }
            if (ExtensionManagementUtility::isLoaded('flux')) {
                $sourceCode .= <<<PHP
                    \$GLOBALS['TCA']['tt_content']['palettes']['$paletteIdentifier']['showitem'] .=
                        ',tx_flux_column,tx_flux_parent';

                    PHP;
            }
        }

        return $sourceCode;
    }

    /**
     * Renders Flexform XML for given DCE
     * using DOMDocument.
     */
    protected function renderFlexformXml(array $singleDceArray): string
    {
        $xml = new \DOMDocument();
        $root = $xml->createElement('T3DataStructure');
        $xml->appendChild($root);

        $meta = $xml->createElement('meta');
        $meta->appendChild($xml->createElement('langDisable', '1'));
        $meta->appendChild($xml->createElement('langDatabaseOverlay', '1'));
        $root->appendChild($meta);

        $sheets = $xml->createElement('sheets');
        foreach ($singleDceArray['tabs'] as $dceTab) {
            $tabRoot = $xml->createElement('ROOT');
            $tab = $xml->createElement('sheet.' . $dceTab['variable']);
            $tab->appendChild($tabRoot);

            $sheetTitle = $xml->createElement('sheetTitle');
            $sheetTitle->appendChild($xml->createCDATASection($dceTab['title']));
            $tabRoot->appendChild($sheetTitle);
            $tabRoot->appendChild($xml->createElement('type', 'array'));

            $tabElements = $xml->createElement('el');
            foreach ($dceTab['fields'] as $dceField) {
                $field = $xml->createElement('settings.' . $dceField['variable']);
                if ('2' === $dceField['type']) {
                    // Section Field
                    $field->appendChild($title = $xml->createElement('title'));
                    $title->appendChild($xml->createCDATASection($dceField['title']));

                    $field->appendChild($tv = $xml->createElement('tx_templatevoila'));
                    $tv->appendChild($title = $xml->createElement('title'));
                    $title->appendChild($xml->createCDATASection($dceField['title']));

                    $field->appendChild($xml->createElement('section', '1'));
                    $field->appendChild($xml->createElement('type', 'array'));

                    $section = $xml->createElement('el');
                    $field->appendChild($section);

                    $sectionContainer = $xml->createElement('container_' . $dceField['variable']);
                    $section->appendChild($sectionContainer);

                    $sectionContainer->appendChild($xml->createElement('type', 'array'));
                    $sectionContainer->appendChild($title = $xml->createElement('title'));
                    $title->appendChild($xml->createCDATASection($dceField['section_fields_tag']));

                    $sectionContainer->appendChild($tv = $xml->createElement('tx_templatevoila'));
                    $tv->appendChild($title = $xml->createElement('title'));
                    $title->appendChild($xml->createCDATASection($dceField['title']));

                    $sectionFields = $xml->createElement('el');
                    foreach ($dceField['section_fields'] as $dceSectionField) {
                        $sectionField = $xml->createElement($dceSectionField['variable']);
                        $sectionFields->appendChild($sectionField);

                        $sectionField->appendChild($label = $xml->createElement('label'));
                        $label->appendChild($xml->createCDATASection($dceSectionField['title']));

                        $conf = new \DOMDocument();
                        $conf->loadXML('<root>' . $dceSectionField['configuration'] . '</root>');

                        /** @var \DOMElement $childNode */
                        foreach ($conf->childNodes[0]->childNodes as $childNode) {
                            $node = $xml->importNode($childNode, true);
                            $sectionField->appendChild($node);
                        }
                    }
                    $sectionContainer->appendChild($sectionFields);
                } else {
                    // Regular fields
                    $field->appendChild($title = $xml->createElement('label'));
                    $title->appendChild($xml->createCDATASection($dceField['title']));

                    $conf = new \DOMDocument();
                    $conf->loadXML('<root>' . $dceField['configuration'] . '</root>');
                    /** @var \DOMElement $firstChildNode */
                    $firstChildNode = $conf->childNodes[0];
                    foreach ($firstChildNode->childNodes as $childNode) {
                        if ($childNode instanceof \DOMElement && 'config' === $childNode->tagName) {
                            foreach ($childNode->childNodes as $childConfigNode) {
                                if ($childConfigNode instanceof \DOMElement && 'dce_skip_translation' === $childConfigNode->tagName) {
                                    $this->applyDisplayCondForSkipTranslation($firstChildNode, $conf);
                                }
                            }
                        }

                        $node = $xml->importNode($childNode, true);
                        $field->appendChild($node);
                    }
                }
                $tabElements->appendChild($field);
                $tabRoot->appendChild($tabElements);
            }
            $sheets->appendChild($tab);
        }
        $root->appendChild($sheets);

        return $xml->saveXML();
    }

    /**
     * Modifies displayCond when "dce_skip_translation" is enabled
     * to hide a field in foreign language, when a l18n_parent is set.
     */
    private function applyDisplayCondForSkipTranslation(\DOMElement $firstChildNode, \DOMDocument $domDocument): void
    {
        $existingValue = null;
        $existingDisplayCond = null;
        // Check for existing displayCond for this field, which need to get merged
        if ($firstChildNode->getElementsByTagName('displayCond')->length > 0) {
            $existingDisplayCond = $firstChildNode->getElementsByTagName('displayCond')->item(0);
            if (isset($existingDisplayCond)) {
                if ($existingDisplayCond->childNodes->item(0) instanceof \DOMText) {
                    $existingValue = $existingDisplayCond->textContent;
                } else {
                    $existingValue = $existingDisplayCond->childNodes;
                }
            }
        }
        // Build new displayCond
        $value1 = $domDocument->createElement('value1', 'FIELD:parentRec.sys_language_uid:=:0');
        $value2 = $domDocument->createElement('value2', 'FIELD:parentRec.l18n_parent:=:0');
        $or = $domDocument->createElement('or');
        $or->appendChild($value1);
        $or->appendChild($value2);

        // Apply existing displayCond (if given)
        $and = null;
        if (null !== $existingValue) {
            if (is_string($existingValue)) {
                $value3 = $domDocument->createElement('value1', $existingValue);
            } else {
                $value3 = $existingValue;
            }
            $and = $domDocument->createElement('and');
            if ($value3 instanceof \DOMNodeList) {
                foreach ($value3 as $value) {
                    $and->appendChild($value);
                }
            } else {
                $and->appendChild($value3);
            }
            $and->appendChild($or);
        }

        // Add new displayCond to XML
        $newNode = $domDocument->createElement('displayCond');
        $newNode->appendChild($and ?? $or);
        $firstChildNode->appendChild($newNode);

        // Remove existing displayCond from XML (if given)
        if ($existingDisplayCond) {
            $existingDisplayCond->parentNode->removeChild($existingDisplayCond);
        }
    }
}
