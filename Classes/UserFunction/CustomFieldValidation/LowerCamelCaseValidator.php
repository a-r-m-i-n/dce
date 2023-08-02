<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2023 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA custom validator which checks lowerCamelCase.
 */
class LowerCamelCaseValidator extends AbstractFieldValidator
{
    public function evaluateFieldValue(string $value, string $isIn, bool $set = false)
    {
        $originalValue = $value;
        $value = lcfirst($value);
        $value = str_replace('-', '_', $value);
        if (false !== strpos($value, '_')) {
            $value = GeneralUtility::underscoredToLowerCamelCase($value);
        }

        if ($originalValue !== $value && !empty($value) && $set) {
            $this->addFlashMessage(
                $this->translate('tx_dce_formeval_lowerCamelCase', [$originalValue, $value]),
                $this->translate('tx_dce_formeval_headline', [$value]),
                FlashMessage::NOTICE
            );
        }

        return $value;
    }
}
