<?php
namespace ArminVieweg\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract class for DCE form validators
 *
 * @package ArminVieweg\Dce
 */
abstract class AbstractFieldValidator
{
    /**
     * JavaScript validation
     *
     * @return string javascript function code for js validation
     */
    public function returnFieldJs()
    {
        return 'return value;';
    }

    /**
     * PHP Validation
     *
     * @param string $value
     * @param bool $silent When true no flash messages should get created
     * @return mixed
     */
    public function evaluateFieldValue($value, $silent = false)
    {
        return $value;
    }

    /**
     * Adds a flash message
     *
     * @param string $message
     * @param string $title optional message title
     * @param int $severity optional severity code
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function addFlashMessage($message, $title = '', $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::OK)
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException(
                'The flash message must be string, ' . gettype($message) . ' given.',
                1243258395
            );
        }

        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            'TYPO3\CMS\Core\Messaging\FlashMessage',
            $message,
            $title,
            $severity,
            true
        );

        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance('TYPO3\CMS\Core\Messaging\FlashMessageService');
        $flashMessageService->getMessageQueueByIdentifier()->addMessage($message);
    }

    /**
     * Returns the translation of current language, stored in locallang_db.xml.
     *
     * @param string $key key in locallang_db.xml to translate
     * @param array $arguments optional arguments
     * @return string Translated text
     */
    protected function translate($key, array $arguments = array())
    {
        return LocalizationUtility::translate(
            'LLL:EXT:dce/Resources/Private/Language/locallang_db.xml:' . $key,
            'Dce',
            $arguments
        );
    }
}
