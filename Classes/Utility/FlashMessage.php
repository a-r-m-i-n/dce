<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility for Flash Messages
 *
 * @package ArminVieweg\Dce
 */
class FlashMessage
{
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    static protected $flashMessageQueue;

    /**
     * @param string $message
     * @param string $title optional
     * @param int $severity
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    public static function add($message, $title = '', $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING)
    {
        if (static::$flashMessageQueue === null) {
            /** @var $flashMessageService FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance('TYPO3\CMS\Core\Messaging\FlashMessageService');
            static::$flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        }

        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            'TYPO3\CMS\Core\Messaging\FlashMessage',
            htmlspecialchars($message),
            $title,
            $severity,
            true
        );
        static::$flashMessageQueue->enqueue($flashMessage);
    }
}
