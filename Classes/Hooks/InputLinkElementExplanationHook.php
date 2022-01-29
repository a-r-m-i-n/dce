<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Provides informations about EXT: path
 * and does not return "not implemented type ext" in typolink wizard.
 *
 * @see \TYPO3\CMS\Backend\Form\Element\InputLinkElement::getLinkExplanation()
 */
class InputLinkElementExplanationHook
{
    public function getFormData(array $linkData): array
    {
        $urlParts = explode('/', $linkData['url']);
        $ext = reset($urlParts);
        $file = end($urlParts);
        if (strlen($file) <= 15 && count($urlParts) > 2) {
            $file = prev($urlParts) . '/' . $file;
        }

        return [
            'text' => $ext . (count($urlParts) > 2 ? '/.../' : '/') . $file,
            'icon' => '',
        ];
    }
}
