<?php
namespace ArminVieweg\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * TCA custom validator which checks the input and disallows leading numbers.
 *
 * @package ArminVieweg\Dce
 */
class NoLeadingNumberValidator extends AbstractFieldValidator
{

    /**
     * PHP Validation to disallow leading numbers
     *
     * @param string $value
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue($value)
    {
        preg_match('/^\d*(.*)/i', $value, $matches);
        if ($matches[0] !== $matches[1]) {
            $this->addFlashMessage(
                $this->translate('tx_dce_formeval_noLeadingNumber', array($value, $matches[1])),
                $this->translate('tx_dce_formeval_headline', array($value)),
                \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
            );
        }
        return $matches[1];
    }
}
