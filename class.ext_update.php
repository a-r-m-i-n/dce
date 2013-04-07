<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Armin Ruediger Vieweg <armin@v.ieweg.de>
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

/**
 * Extension update script
 * Needed for version 0.8.0, just for existing dce instances
 */
class ext_update  {
	/** @var array */
	protected $output = array();

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return string HTML
	 */
	public function main() {
		$this->updatingDceInstancesWithoutRelation();

		return $this->getOutput();
	}

	/**
	 * Checks existing dce instances if the new column "tx_dce_dce" in tt_content is zero. Updates this column
	 * using the CType.
	 *
	 * @return void
	 */
	protected function updatingDceInstancesWithoutRelation() {
		$title = 'Updating dce instances with missing relation to dce (new column: "tx_dce_dce")';
		$version = '0.8.0';

		$dceInstancesToUpdate = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,CType,tx_dce_dce', 'tt_content', 'CType LIKE "dce_dceuid%" AND tx_dce_dce=0');
		foreach($dceInstancesToUpdate as $dceInstanceRow) {
			$dceInstanceRow['tx_dce_dce'] = Tx_Dce_Domain_Repository_DceRepository::extractUidFromCType($dceInstanceRow['CType']);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid=' . $dceInstanceRow['uid'], $dceInstanceRow);
		}
		$this->addOutput(count($dceInstancesToUpdate) . ' dce instances has been updated.', $title, $version);
	}

	/**
	 * @return boolean Always returns true
	 */
	public function access() {
		return true;
	}

	/**
	 * Add entry to output log
	 *
	 * @param string $result
	 * @param string $title
	 * @param string $version
	 * @return void
	 */
	protected function addOutput($result, $title, $version) {
		$this->output[] = array(
			'title' => $title,
			'result' => $result,
			'version' => $version,
			'microtime' => microtime(TRUE),
		);
	}

	/**
	 * Returns the output entries as html
	 * @return string html output
	 */
	protected function getOutput() {
		$output = '<ul>';
		foreach($this->output as $outputRecord) {
			$output .= '<li><h4><span>' . $outputRecord['title'] . '</span> <em>[' . $outputRecord['version'] . ']</em></h4><p>' . $outputRecord['result'] . '</p></li>';
		}
		$output .= '</ul>';
		return $output;
	}

}
?>