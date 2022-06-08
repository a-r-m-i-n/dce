<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Service\FlexFormService as CoreFlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Returns correct FlexFormService (TYPO3 8/9 compatibility).
 */
class FlexformService
{
    public static function get(): CoreFlexFormService
    {
        /** @var CoreFlexFormService $flexFormService */
        $flexFormService = GeneralUtility::makeInstance(CoreFlexFormService::class);

        return $flexFormService;
    }

    /**
     * @param \DOMElement|\DOMDocument$root
     * @return array|string
     */
    public static function xmlToArray($root)
    {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length === 1) {
                $child = $children->item(0);
                if ($child->nodeType === XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) === 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = self::xmlToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::xmlToArray($child);
                }
            }
        }

        return $result;
    }
}
