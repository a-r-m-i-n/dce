<?php
namespace ArminVieweg\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
     * @param bool $silent When true no flash messages get created
     * @return mixed|string Updated string, which fits the requirements
     */
    public function evaluateFieldValue($value, $silent = false)
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
                    \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
                );
            }
        }
        return $matches[1];
    }
}
