<?php
namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DCE - Content Element Generator
 * Generates content elements in TYPO3 based on given DCE configuration.
 */
class Generator
{
    public const CACHE_NAME = 'dce_cache';

    /**
     * @var InputDatabase
     */
    protected $inputDatabase;

    /**
     * @var OutputPlugin
     */
    protected $outputPlugin;

    /**
     * @var OutputTcaAndFlexForm
     */
    protected $outputTcaAndFlexForm;

    /**
     * Generator constructor
     */
    public function __construct()
    {
        $this->inputDatabase = GeneralUtility::makeInstance(InputDatabase::class);
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $cacheManager->getCache(self::CACHE_NAME);

        $this->outputPlugin = GeneralUtility::makeInstance(
            OutputPlugin::class,
            $this->inputDatabase,
            $cache
        );
        $this->outputTcaAndFlexForm = GeneralUtility::makeInstance(
            OutputTcaAndFlexForm::class,
            $this->inputDatabase,
            $cache
        );
    }

    /**
     * @return void
     */
    public function makeTca() : void
    {
        $this->outputTcaAndFlexForm->generate();
    }

    /**
     * @return void
     */
    public function makePluginConfiguration() : void
    {
        $this->outputPlugin->generate();
    }
}
