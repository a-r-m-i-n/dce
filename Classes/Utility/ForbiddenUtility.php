<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Stuff in here is just here, because I have no clue how to
 * realize it, without these commands. Coding guidelines doesn't
 * like this stuff, so I've excluded this file from checks.
 *
 * Never put code in here! And if you do, you should feel really bad!
 *
 * @package ArminVieweg\Dce
 */
class ForbiddenUtility
{

    /**
     * If these post methods are missing, rendering fluid template in
     * backend context fails. Don't know why, but this works.
     *
     * @param string $controller
     * @param string $action
     * @return void
     */
    public static function setExtbaseRelatedPostParameters($controller, $action)
    {
        $_POST['tx_dce_tools_dcedcemodule']['controller'] = $controller;
        $_POST['tx_dce_tools_dcedcemodule']['action'] = $action;
    }
}
