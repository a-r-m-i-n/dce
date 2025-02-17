<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * TCA custom validator which checks the input and disallows leading numbers.
 */
class NoLeadingNumberValidator extends AbstractFieldValidator
{
    public function evaluateFieldValue(string $value, bool $set = false)
    {
        preg_match('/^\d*(.*)/i', $value, $matches);
        if ($matches[0] !== $matches[1]) {
            if (empty($matches[1])) {
                $matches[1] = 'field' . uniqid('', true);
            }
            if ($set) {
                $this->addFlashMessage(
                    $this->translate('tx_dce_formeval_noLeadingNumber', [$value, $matches[1]]),
                    $this->translate('tx_dce_formeval_headline', [$value]),
                    ContextualFeedbackSeverity::NOTICE
                );
            }
        }

        return $matches[1];
    }
}
