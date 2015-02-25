<?php
namespace DceTeam\Dce\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility for Flash Messages
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class FlashMessage {
	/**
	 * @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue
	 */
	static protected $flashMessageQueue;

	/**
	 * @param string $message
	 * @param string $title optional
	 * @param int $severity
	 * @throws \TYPO3\CMS\Core\Exception
	 */
	static public function add($message, $title = '', $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING) {
		if (static::$flashMessageQueue === NULL) {

			/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
			$flashMessageService = GeneralUtility::makeInstance('TYPO3\CMS\Core\Messaging\FlashMessageService');
			/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
			static::$flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		}

		/** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
		$flashMessage = GeneralUtility::makeInstance('TYPO3\CMS\Core\Messaging\FlashMessage', htmlspecialchars($message), $title, $severity, TRUE);
		static::$flashMessageQueue->enqueue($flashMessage);
	}

}