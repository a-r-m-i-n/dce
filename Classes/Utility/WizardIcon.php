<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        if (!GeneralUtility::compat_version('7.6')) {
            $identifiers = self::getIconIdentifiersFor62();
        }

        $items = array();
        foreach ($identifiers as $identifier) {
            $items[] = array(
                'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:wizardIcon.' . $identifier,
                $identifier,
                !GeneralUtility::compat_version('7.6') ? 'c_wiz/' . $identifier . '.gif' : $identifier
            );
        }

        if ($withExtraRowForCustomIcon) {
            $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:';
            $items[] = array($ll . 'wizardIcon.custom', '--div--');
            $items[] = array($ll . 'wizardIcon.customIcon', 'custom');
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
        return array(
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
        );
    }

    /**
     * @return array
     * @deprecated Will be removed when 6.2 support runs out
     */
    public static function getIconIdentifiersFor62()
    {
        return array(
            'regular_text',
            'text_image_right',
            'text_image_below',
            'bullet_list',
            'div',
            'filelinks',
            'mailform',
            'html',
            'shortcut',
            'multimedia',
            'sitemap2',
            'sitemap',
            'images_only',
            'login_form',
            'searchform',
            'table',
            'user_defined'
        );
    }
}
