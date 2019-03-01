<?php
namespace T3\Dce\Hooks;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Provides informations about EXT: path
 * and does not return "not implemented type ext" in typolink wizard
 *
 * @see \TYPO3\CMS\Backend\Form\Element\InputLinkElement::getLinkExplanation()
 */
class InputLinkElementExplanationHook
{
    /**
     * @param array $linkData
     * @return array
     */
    public function getFormData(array $linkData) : array
    {
        $urlParts = explode('/', $linkData['url']);
        $ext = reset($urlParts);
        $file = end($urlParts);
        if (strlen($file) <= 15) {
            $file = prev($urlParts) . '/' . $file;
        }
        return [
            'text' => $ext . '/.../' . $file,
            'icon' => ''
        ];
    }
}
