<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA custom validator which checks lowerCamelCase.
 */
class LowerCamelCaseValidator extends AbstractFieldValidator
{
    /**
     * PHP Validation to check lowerCamelCase.
     *
     * @param bool $silent When true no flash messages get created
     *
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue(string $value, bool $silent = false)
    {
        $originalValue = $value;
        $value = lcfirst($value);
        $value = str_replace('-', '_', $value);
        if (false !== strpos($value, '_')) {
            $value = GeneralUtility::underscoredToLowerCamelCase($value);
        }

        if ($originalValue !== $value && !empty($value) && !$silent) {
            $this->addFlashMessage(
                $this->translate('tx_dce_formeval_lowerCamelCase', [$originalValue, $value]),
                $this->translate('tx_dce_formeval_headline', [$value]),
                FlashMessage::NOTICE
            );
        }

        return $value;
    }
}
