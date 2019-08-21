<?php
namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Compatibility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Simplified CacheManager for generated DCE code.
 * For PHP code only!
 */
class CacheManager implements SingletonInterface
{
    public const CACHE_NAME = 'cache_dce';

    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var bool When enabled is false, cache is still written and removed, but does never return values from cache
     */
    private $enabled = true;


    public function __construct(string $cacheName = self::CACHE_NAME)
    {
        $this->cachePath = Compatibility::getVarPath() . $this->getCachePath($cacheName);
        if (!file_exists($this->cachePath)) {
            $status = mkdir($this->cachePath, 0777, true);
            if (!$status || !file_exists($this->cachePath) || !is_dir($this->cachePath)) {
                throw new \RuntimeException('Unable to create cache directory "', $this->cachePath . '"!');
            }
        }

        if (isset($GLOBALS['TYPO3_CONF_VARS']['USER']['disable_dce_code_cache']) &&
            $GLOBALS['TYPO3_CONF_VARS']['USER']['disable_dce_code_cache']
        ) {
            $this->enabled = false;
        }
    }

    /**
     * Builds cache path, based on current TYPO3 version.
     *
     * @param string $cacheName
     * @return string
     */
    protected function getCachePath(string $cacheName)
    {
        $cacheSegment = 'cache';
        $codeSegment = 'code';
        // Remove this when 8.7 support is dropped
        if (!Compatibility::isTypo3Version()) {
            $cacheSegment = ucfirst($cacheSegment);
            $codeSegment = ucfirst($codeSegment);
        }
        return DIRECTORY_SEPARATOR . $cacheSegment .
               DIRECTORY_SEPARATOR . $codeSegment .
               DIRECTORY_SEPARATOR . $cacheName . DIRECTORY_SEPARATOR;
    }


    protected function buildCacheFilePathByKey(string $key): string
    {
        return $this->cachePath . $key . '.php';
    }

    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }
        return file_exists($this->buildCacheFilePathByKey($key));
    }

    public function get(string $key): ?string
    {
        if ($this->has($key)) {
            return file_get_contents($this->buildCacheFilePathByKey($key));
        }
        return null;
    }

    public function set(string $key, $value): void
    {
        $path = $this->buildCacheFilePathByKey($key);
        if ($this->has($key)) {
            $this->remove($key);
        }

        // Prepend php opening tag and append empty comment line
        $value = '<?php' . PHP_EOL. PHP_EOL . $value . PHP_EOL . PHP_EOL . '#' . PHP_EOL;

        $status = file_put_contents($path, $value);
        if (!$status) {
            throw new \RuntimeException(
                'Unable to write to cache file "' . $path . '"! Please check write permissions.'
            );
        }
    }

    /**
     * Requires given PHP cache file
     *
     * @param string $key
     */
    public function requireOnce(string $key): void
    {
        $path = $this->buildCacheFilePathByKey($key);
        if (!file_exists($path)) {
            throw new \RuntimeException(
                'Unable to require cache file "' . $path . '"! Please ensure to write cache file, before accessing it.'
            );
        }
        require_once $path;
    }

    /**
     * Removes cache item, if existing.
     *
     * @param string $key
     */
    public function remove(string $key): void
    {
        $path = $this->buildCacheFilePathByKey($key);
        if (file_exists($path)) {
            $status = unlink($path);
            if (!$status || file_exists($path)) {
                throw new \RuntimeException(
                    'Unable to delete old cache file "' . $path  . '"! Please check write permissions.'
                );
            }
        }
    }

    /**
     * Removes/flushes all cached items in current CACHE_NAME scope.
     */
    public function flush(): void
    {
        $cacheDir = $this->cachePath;
        $files = GeneralUtility::getFilesInDir($cacheDir, 'php');
        foreach ($files as $file) {
            $key = substr($file, 0, -4);
            $this->remove($key);
        }
    }

    public static function makeInstance(string $cacheName = self::CACHE_NAME): self
    {
        return GeneralUtility::makeInstance(__CLASS__, $cacheName);
    }
}
