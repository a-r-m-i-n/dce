<?php

declare(strict_types = 1);

namespace T3\Dce\Utility;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use T3\Dce\Domain\Model\Dce;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DceExpressionUtility implements SingletonInterface
{
    /**
     * @var ExpressionLanguage|null
     */
    private $expressionLangauge;

    public function evaluate(string $expression, Dce $dce): ?string
    {
        $this->expressionLangauge = $this->expressionLangauge ?? GeneralUtility::makeInstance(ExpressionLanguage::class);

        $result = $this->expressionLangauge->evaluate(
            $expression,
            [
                'dce' => $dce,
                'contentObject' => $dce->getContentObject(),
            ] + $dce->getGet()
        );

        return $result ? (string)$result : null;
    }
}
