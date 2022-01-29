<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * TCA custom validator which checks the input and disallows leading numbers.
 */
class NoLeadingNumberValidator extends AbstractFieldValidator
{
    /**
     * PHP Validation to disallow leading numbers.
     *
     * @param bool $silent When true no flash messages get created
     *
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue(string $value, bool $silent = false)
    {
        preg_match('/^\d*(.*)/i', $value, $matches);
        if ($matches[0] !== $matches[1]) {
            if (empty($matches[1])) {
                $matches[1] = 'field' . uniqid();
            }
            if (!$silent) {
                $this->addFlashMessage(
                    $this->translate('tx_dce_formeval_noLeadingNumber', [$value, $matches[1]]),
                    $this->translate('tx_dce_formeval_headline', [$value]),
                    FlashMessage::NOTICE
                );
            }
        }

        return $matches[1];
    }
}
