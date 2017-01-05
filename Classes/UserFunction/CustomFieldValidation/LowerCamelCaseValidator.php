<?php
namespace ArminVieweg\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * TCA custom validator which checks lowerCamelCase.
 *
 * @package ArminVieweg\Dce
 */
class LowerCamelCaseValidator extends AbstractFieldValidator
{
    /**
     * PHP Validation to check lowerCamelCase
     *
     * @param string $value
     * @param bool $silent When true no flash messages get created
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue($value, $silent = false)
    {
        $originalValue = $value;
        $value = lcfirst($value);
        $value = str_replace('-', '_', $value);
        if (strpos($value, '_') !== false) {
            $value = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($value);
        }

        if ($originalValue !== $value && !empty($value) && !$silent) {
            $this->addFlashMessage(
                $this->translate('tx_dce_formeval_lowerCamelCase', [$originalValue, $value]),
                $this->translate('tx_dce_formeval_headline', [$value]),
                \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
            );
        }
        return $value;
    }
}
