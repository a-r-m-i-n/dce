<?php

namespace T3\Dce\Components\UserConditions;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

/**
 * Required class to register TypoScriptConditionFunctionProvider.
 */
class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            TypoScriptConditionFunctionProvider::class,
        ];
    }
}
