<?php
namespace ArminVieweg\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue($value)
    {
        $originalValue = $value;
        $value = str_replace('-', '_', $value);
        if (strpos($value, '_') !== false) {
            $value = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($value);
        }

        if ($originalValue !== $value && !empty($value)) {
            $this->addFlashMessage(
                $this->translate('tx_dce_formeval_lowerCamelCase', array($originalValue, $value)),
                $this->translate('tx_dce_formeval_headline', array($value)),
                \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
            );
        }
        return $value;
    }
}
