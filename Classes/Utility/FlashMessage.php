<?php
namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility for Flash Messages
 */
class FlashMessage
{
    /**
     * @var FlashMessageQueue
     */
    protected static $flashMessageQueue;

    /**
     * @param string $message
     * @param string $title optional
     * @param int $severity
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    public static function add(
        string $message,
        string $title = '',
        int $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING
    ) : void {
        if (static::$flashMessageQueue === null) {
            /** @var $flashMessageService FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            static::$flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        }

        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessage::class,
            htmlspecialchars($message),
            $title,
            $severity,
            true
        );
        static::$flashMessageQueue->enqueue($flashMessage);
    }
}
