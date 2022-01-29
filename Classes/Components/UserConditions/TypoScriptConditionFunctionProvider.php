<?php

namespace T3\Dce\Components\UserConditions;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides the expression function "dceOnCurrentPage"
 * which points to "DceOnCurrentPage" condition used in TYPO3 8.7.
 */
class TypoScriptConditionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            $this->getDceOnCurrentPageFunction(),
        ];
    }

    protected function getDceOnCurrentPageFunction(): ExpressionFunction
    {
        return new ExpressionFunction('dceOnCurrentPage', function () {
            // Not implemented, we only use the evaluator
        }, function (array $arguments, string $str) {
            $condition = GeneralUtility::makeInstance(DceOnCurrentPage::class);

            return $condition->matchCondition([$str, $arguments]);
        });
    }
}
