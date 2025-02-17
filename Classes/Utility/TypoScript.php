<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class TypoScript
{
    public function __construct(private readonly TypoScriptService $typoScriptService)
    {
    }

    public function getTypoScriptSetupArray(): ?array
    {
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            return null;
        }

        /** @var FrontendTypoScript|null $frontendTypoScript */
        $frontendTypoScript = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript');
        if (!$frontendTypoScript) {
            return null;
        }

        return $this->typoScriptService->convertTypoScriptArrayToPlainArray($frontendTypoScript->getSetupArray());
    }
}
