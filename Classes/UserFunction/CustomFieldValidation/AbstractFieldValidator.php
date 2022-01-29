<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract class for DCE form validators.
 */
abstract class AbstractFieldValidator
{
    /**
     * JavaScript validation.
     *
     * @return string javascript function code for js validation
     */
    public function returnFieldJs(): string
    {
        return 'return value;';
    }

    /**
     * PHP Validation.
     *
     * @param bool $silent When true no flash messages should get created
     *
     * @return mixed
     */
    public function evaluateFieldValue(string $value, bool $silent = false)
    {
        return $value;
    }

    /**
     * Adds a flash message.
     *
     * @param string $message
     * @param string $title    optional message title
     * @param int    $severity optional severity code
     *
     * @throws \InvalidArgumentException
     */
    protected function addFlashMessage($message, $title = '', $severity = FlashMessage::OK): void
    {
        if (!\is_string($message)) {
            throw new \InvalidArgumentException('The flash message must be string, ' . \gettype($message) . ' given.', 1243258395);
        }

        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->addMessage($message);
    }

    /**
     * Returns the translation of current language, stored in locallang_db.xlf.
     *
     * @param string $key       key in locallang_db.xlf to translate
     * @param array  $arguments optional arguments
     *
     * @return string Translated text
     */
    protected function translate(string $key, array $arguments = []): string
    {
        return LocalizationUtility::translate(
            'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:' . $key,
            'Dce',
            $arguments
        );
    }
}
