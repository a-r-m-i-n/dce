<?php

class tx_dce_formevals_lowerCamelCase {

	function returnFieldJS() {
	}

	function evaluateFieldValue($value, $is_in = '', $set = '') {
		$value = str_replace('-', '_', $value);

		while (strpos($value, '_') !== FALSE) {
			$position = strpos($value, '_') + 1;
			$value = substr($value, 0, $position - 1) . strtoupper(substr($value, $position, 1)) . substr($value, $position + 1);
		}

		$value = str_replace('_', '', $value);
		$value{0} = strtolower($value{0});
		return $value;
	}
}
?>