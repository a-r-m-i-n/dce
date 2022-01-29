<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Utility for TypoScript.
 */
class TypoScript
{
    /**
     * Content Object Renderer.
     *
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * Configuration Manager.
     *
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager = null;

    /**
     * Injects the configurationManager.
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Initialize this settings utility.
     */
    public function initializeObject(): void
    {
        $this->contentObject = $this->configurationManager->getContentObject();
    }

    /**
     * Converts given TypoScript string to array.
     *
     * @param string $typoScriptString Typoscript text piece
     * @param bool   $returnPlainArray if TRUE a plain array will be returned
     */
    public function parseTypoScriptString(string $typoScriptString, bool $returnPlainArray = false): array
    {
        /** @var TypoScriptParser $typoScriptParser */
        $typoScriptParser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $typoScriptParser->parse($typoScriptString);
        if (false === $returnPlainArray) {
            return $typoScriptParser->setup;
        }

        return $this->convertTypoScriptArrayToPlainArray($typoScriptParser->setup);
    }

    /**
     * Converts given array to TypoScript.
     *
     * @param array  $typoScriptArray The array to convert to string
     * @param string $addKey          Prefix given values with given key
     *                                (eg. lib.whatever = {...})
     * @param int    $tab             Internal
     * @param bool   $init            Internal
     *
     * @return string TypoScript
     */
    public function convertArrayToTypoScript(
        array $typoScriptArray,
        string $addKey = '',
        int $tab = 0,
        bool $init = true
    ): string {
        $typoScript = '';
        if ('' !== $addKey) {
            $typoScript .= str_repeat("\t", (0 === $tab) ? $tab : $tab - 1) . $addKey . " {\n";
            if (true === $init) {
                ++$tab;
            }
        }
        ++$tab;
        foreach ($typoScriptArray as $key => $value) {
            if (!\is_array($value)) {
                if (false === strpos($value, "\n")) {
                    $typoScript .= str_repeat("\t", (0 === $tab) ? $tab : $tab - 1) . $key . ' = ' . $value . "\n";
                } else {
                    if ('configuration' === $key) {
                        $valueLines = explode("\n", $value);
                        $indentedValueLines = [];
                        foreach ($valueLines as $valueLine) {
                            $indentedValueLines[] = str_repeat("\t", $tab) . $valueLine;
                        }
                        $value = implode("\n", $indentedValueLines);
                    }
                    $tabAmount = (0 === $tab) ? $tab : $tab - 1;
                    $typoScript .= str_repeat("\t", $tabAmount) . $key . " (\n" . $value . "\n" .
                        str_repeat("\t", $tabAmount) . ")\n";
                }
            } else {
                $typoScript .= $this->convertArrayToTypoScript($value, $key, $tab, false);
            }
        }
        if ('' !== $addKey) {
            --$tab;
            $typoScript .= str_repeat("\t", (0 === $tab) ? $tab : $tab - 1) . '}';
            if (true !== $init) {
                $typoScript .= "\n";
            }
        }

        return $typoScript;
    }

    /**
     * Converts given typoScriptArray to plain array.
     *
     * @return array plain array
     */
    public function convertTypoScriptArrayToPlainArray(array $typoScriptArray): array
    {
        /** @var TypoScriptService $typoScriptService */
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);

        return $typoScriptService->convertTypoScriptArrayToPlainArray($typoScriptArray);
    }

    /**
     * Renders a given typoscript configuration and returns the whole array with
     * calculated values.
     *
     * @param array $settings the typoscript configuration array
     *
     * @return array the configuration array with the rendered typoscript
     */
    public function renderConfigurationArray(array $settings): array
    {
        $settings = $this->enhanceSettingsWithTypoScript($this->makeConfigurationArrayRenderable($settings));
        $result = [];

        foreach ($settings as $key => $value) {
            if ('.' === substr($key, -1)) {
                $keyWithoutDot = substr($key, 0, -1);
                if (array_key_exists($keyWithoutDot, $settings)) {
                    $result[$keyWithoutDot] = $this->contentObject->cObjGetSingle(
                        $settings[$keyWithoutDot],
                        $value
                    );
                } else {
                    $result[$keyWithoutDot] = $this->renderConfigurationArray($value);
                }
            } else {
                if (!array_key_exists($key . '.', $settings)) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Overwrite flexform values with typoscript if flexform value is empty and
     * typoscript value exists.
     *
     * @param array $settings Settings from flexform
     *
     * @return array enhanced settings
     */
    protected function enhanceSettingsWithTypoScript(array $settings): array
    {
        $extkey = 'tx_dce';
        $typoscript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $typoscript = $typoscript['plugin.'][$extkey . '.']['settings.'] ?? [];
        foreach ($settings as $key => $setting) {
            if ('' === $setting && \is_array($typoscript) && array_key_exists($key, $typoscript)) {
                $settings[$key] = $typoscript[$key];
            }
        }

        return $settings;
    }

    /**
     * Formats a given array with typoscript syntax, recursively. After the
     * transformation it can be rendered with cObjGetSingle.
     *
     * Example:
     * Before: $array['level1']['level2']['finalLevel'] = 'hello kitty'
     * After:  $array['level1.']['level2.']['finalLevel'] = 'hello kitty'
     *         $array['level1'] = 'TEXT'
     *
     * @param array $configuration settings array to make renderable
     *
     * @return array the renderable settings
     */
    protected function makeConfigurationArrayRenderable(array $configuration): array
    {
        $dottedConfiguration = [];
        foreach ($configuration as $key => $value) {
            if (\is_array($value)) {
                if (array_key_exists('_typoScriptNodeValue', $value)) {
                    $dottedConfiguration[$key] = $value['_typoScriptNodeValue'];
                }
                $dottedConfiguration[$key . '.'] = $this->makeConfigurationArrayRenderable($value);
            } else {
                $dottedConfiguration[$key] = $value;
            }
        }

        return $dottedConfiguration;
    }
}
