<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Utility for wizard icons
 *
 * @package ArminVieweg\Dce
 */
class WizardIcon
{
    /**
     * Returns list of available wizard icons.
     * Ready to use in TCA array ['item'] attribute of selects.
     *
     * @param bool $withExtraRowForCustomIcon When true a --div-- and a another record will be added
     * @return array
     */
    public static function getTcaListItems($withExtraRowForCustomIcon = true)
    {
        $identifiers = self::getIconIdentifiers();

        $items = [];
        foreach ($identifiers as $identifier) {
            $items[] = [
                'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:wizardIcon.' . $identifier,
                $identifier,
                $identifier
            ];
        }

        if ($withExtraRowForCustomIcon) {
            $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:';
            $items[] = [$ll . 'wizardIcon.custom', '--div--'];
            $items[] = [$ll . 'wizardIcon.customIcon', 'custom'];
        }
        return $items;
    }

    /**
     * Returns icon identifiers available nativley by the system
     *
     * @return array with strings
     */
    public static function getIconIdentifiers()
    {
        return [
            'content-header',
            'content-textpic',
            'content-bullets',
            'content-table',
            'content-special-uploads',
            'content-special-menu',
            'content-special-html',
            'content-special-div',
            'content-special-shortcut',
            'content-elements-login',
            'content-elements-mailform',
            'content-plugin'
        ];
    }
}
