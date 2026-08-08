<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class TypoScript
{
    public function __construct(private readonly TypoScriptService $typoScriptService)
    {
    }

    public function getTypoScriptSetupArray(?ServerRequestInterface $request): ?array
    {
        if (null === $request) {
            return null;
        }

        /** @var FrontendTypoScript|null $frontendTypoScript */
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        if (!$frontendTypoScript) {
            return null;
        }

        return $this->typoScriptService->convertTypoScriptArrayToPlainArray($frontendTypoScript->getSetupArray());
    }
}
