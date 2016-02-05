<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class for the greatest and only existing extension-framework for TYPO3
 *
 * @package ArminVieweg\Dce
 */
class Extbase
{
    /**
     * Initializes and runs an extbase controller
     *
     * @param string $vendorName Name of vendor
     * @param string $extensionName Name of extension, in UpperCamelCase
     * @param string $controller Name of controller, in UpperCamelCase
     * @param string $action Optional name of action, in lowerCamelCase (without 'Action' suffix). Default is 'index'.
     * @param string $pluginName Optional name of plugin. Default is 'Pi1'.
     * @param array $settings Optional array of settings to use in controller and fluid template. Default is array().
     * @param bool $compressedObject When true a compressed, serialized object is expected from Extbase return value.
     * @return mixed output of controller's action
     */
    public static function bootstrapControllerAction(
        $vendorName,
        $extensionName,
        $controller,
        $action = 'index',
        $pluginName = 'Pi1',
        $settings = array(),
        $compressedObject = false
    ) {
        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $configuration = array(
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'controller' => $controller,
            'action' => $action,
            'pluginName' => $pluginName,
            'settings' => $settings
        );
        \ArminVieweg\Dce\Utility\ForbiddenUtility::setExtbaseRelatedPostParameters($controller, $action);
        $extbaseReturnValue = $bootstrap->run('', $configuration);
        unset($bootstrap);

        if ($compressedObject) {
            return unserialize(gzuncompress($extbaseReturnValue));
        }
        return $extbaseReturnValue;
    }
}
