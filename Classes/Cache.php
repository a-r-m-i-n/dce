<?php
namespace ArminVieweg\Dce;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Cache Generator
 *
 * Creates ext_localconf.php and ext_tables.php in /typo3temp/Cache/Code/cache_dce
 * Both files contain the whole DCE configuration used natively by TYPO3.
 *
 * Fluid Template Engine is used to render the files. You find the templates in
 * EXT:dce/Resources/Private/Templates/DceSource/
 *
 * Flexform configuration is outsourced to partial.
 *
 * @package ArminVieweg\Dce
 */
class Cache
{
    /**
     * Path of DCE cache files
     */
    const CACHE_PATH = 'typo3temp/var/Cache/Code/cache_dce/';

    /**
     * Filename for cache type ext_localconf
     */
    const CACHE_TYPE_EXTLOCALCONF = 'ext_localconf.php';

    /**
     * Filename for cache type ext_tables
     */
    const CACHE_TYPE_EXTTABLES = 'ext_tables.php';

    /**
     * @var \ArminVieweg\Dce\Utility\FluidTemplate
     */
    protected $fluidTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fluidTemplate = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\FluidTemplate');
    }

    /**
     * Renders and saves ext_localconf.php contents
     *
     * @return void
     */
    public function createLocalconf()
    {
        if (!$this->isDatabaseValid()) {
            return;
        }

        $this->fluidTemplate->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/localconf.html'
        );

        $this->fluidTemplate->assign('dceArray', $this->getDatabaseDce());
        $this->saveCacheData(self::CACHE_TYPE_EXTLOCALCONF, $this->fluidTemplate->render());
    }

    /**
     * Renders and saves ext_tables.php contents
     *
     * @return void
     */
    public function createExtTables()
    {
        if (!$this->isDatabaseValid()) {
            return;
        }

        $this->fluidTemplate->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/ext_tables.html'
        );

        $dces = $this->getDatabaseDce();
        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $dces = $this->ensureGridelementsFieldCompatibility($dces);
        }
        $this->fluidTemplate->assign('dceArray', $dces);

        $this->fluidTemplate->assign(
            'dceFieldsWithNewTcaColumns',
            array_unique(Components\FlexformToTcaMapper\Mapper::getDceFieldRowsWithNewTcaColumns())
        );
        $this->saveCacheData(self::CACHE_TYPE_EXTTABLES, $this->fluidTemplate->render());
    }

    /**
     * Initializes database and checks if required DCE tables are present
     *
     * @return bool
     */
    protected function isDatabaseValid()
    {
        $db = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        return ($db->admin_get_fields('tx_dce_domain_model_dce') &&
                $db->admin_get_fields('tx_dce_domain_model_dcefield')
        );
    }

    /**
     * Stores given data under given path, create folder if not existing and fixes file permissions.
     *
     * @param string $cacheType Filename of cache file
     * @param string $data Content to write to cache
     * @return void
     * @throws \Exception
     */
    protected function saveCacheData($cacheType, $data)
    {
        self::validateCacheType($cacheType);
        self::checkCacheBasePath();

        $cachePath = PATH_site . self::CACHE_PATH . $cacheType;
        if (is_writable(dirname($cachePath))) {
            file_put_contents($cachePath, $data);
            GeneralUtility::fixPermissions($cachePath);
        } else {
            throw new \Exception('Not able to write to cache file "' . $cachePath . '"!', 1438174706);
        }
    }

    /**
     * Clears the DCE cache
     *
     * @return void
     */
    public static function clear()
    {
        $cachePath = PATH_site . self::CACHE_PATH;
        if (file_exists($cachePath . self::CACHE_TYPE_EXTLOCALCONF)) {
            unlink($cachePath . self::CACHE_TYPE_EXTLOCALCONF);
        }
        if (file_exists($cachePath . self::CACHE_TYPE_EXTTABLES)) {
            unlink($cachePath . self::CACHE_TYPE_EXTTABLES);
        }
    }

    /**
     * Checks if given cache type is valid. If not an exception will be thrown.
     *
     * @param string $cacheType
     * @return void
     * @throws \UnexpectedValueException
     */
    protected static function validateCacheType($cacheType)
    {
        if ($cacheType !== self::CACHE_TYPE_EXTLOCALCONF && $cacheType !== self::CACHE_TYPE_EXTTABLES) {
            throw new \UnexpectedValueException(
                'Given cache type not allowed. Use \ArminVieweg\Dce\Cache::CACHE_TYPE_* constants only.',
                1438174705
            );
        }
    }

    /**
     * Checks if given cacheType exists
     *
     * @param string $cacheType
     * @return bool true if cache already exists, false if not
     */
    public static function cacheExists($cacheType)
    {
        self::validateCacheType($cacheType);
        return file_exists(PATH_site . self::CACHE_PATH . $cacheType);
    }

    /**
     * Checks if expected folder for DCE cache is exsting. If not
     * it creates the folder.
     */
    protected function checkCacheBasePath()
    {
        $cachePath = PATH_site . self::CACHE_PATH;
        if (!file_exists($cachePath) || !is_dir($cachePath)) {
            GeneralUtility::mkdir_deep($cachePath);
        }
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
     */
    protected function getDatabaseDce()
    {
        /** @var $databaseConnection \TYPO3\CMS\Dbal\Database\DatabaseConnection */
        $databaseConnection = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();

        $tables = array_keys($databaseConnection->admin_get_tables());
        if (!in_array('tx_dce_domain_model_dce', $tables) || !in_array('tx_dce_domain_model_dcefield', $tables)) {
            return [];
        }

        $res = $databaseConnection->exec_SELECTquery(
            '*',
            'tx_dce_domain_model_dce',
            'deleted=0 AND pid=0',
            '',
            'sorting asc'
        );

        $dce = [];
        while (($row = $databaseConnection->sql_fetch_assoc($res))) {
            $res2 = $databaseConnection->exec_SELECTquery(
                '*',
                'tx_dce_domain_model_dcefield',
                'parent_dce = ' . $row['uid'] . ' AND deleted=0 AND hidden=0',
                '',
                'sorting asc'
            );

            if (TYPO3_MODE === 'FE') {
                $generalTabLabel = LocalizationUtility::translate('generaltab', 'dce');
            } else {
                $generalTabLabel = LocalizationUtility::translate(
                    'LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab',
                    'dce'
                );
            }
            $tabs = [0 => ['title' => $generalTabLabel, 'variable' => 'tabGeneral', 'fields' => []]];
            $index = 0;
            while ($row2 = $databaseConnection->sql_fetch_assoc($res2)) {
                if ($row2['type'] === '1') {
                    // Create new Tab
                    $index++;
                    $tabs[$index] = [];
                    $tabs[$index]['title'] = $row2['title'];
                    $tabs[$index]['variable'] = $row2['variable'];
                    $tabs[$index]['fields'] = [];
                    continue;
                } elseif ($row2['type'] === '2') {
                    $res3 = $databaseConnection->exec_SELECTquery(
                        '*',
                        'tx_dce_domain_model_dcefield',
                        'parent_field = ' . $row2['uid'] . ' AND deleted=0 AND hidden=0',
                        '',
                        'sorting asc'
                    );

                    $sectionFields = [];
                    while (($row3 = $databaseConnection->sql_fetch_assoc($res3))) {
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
            if (count($tabs[0]['fields']) === 0) {
                unset($tabs[0]);
            }

            $row['tabs'] = $tabs;
            $row['hasCustomWizardIcon'] = ($row['wizard_icon'] === 'custom') ? true : false;
            $dce[] = $row;
        }
        return $dce;
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
            if (!in_array('colPos', $paletteFields)) {
                $paletteFields[] = 'colPos';
            }
            $dces[$key]['palette_fields'] = implode(', ', $paletteFields);
        }
        return $dces;
    }
}
