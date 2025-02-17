<?php

namespace T3\Dce\UserFunction\CustomFieldValidation;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract class for DCE form validators.
 */
abstract class AbstractFieldValidator
{
    protected function addFlashMessage(string $message, string $title = '', ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK): void
    {
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
