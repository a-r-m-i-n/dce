<?php
$extensionPath = t3lib_extMgm::extPath('dce');
return array(
	'tx_dce_controller_dcemodulecontroller' => $extensionPath . 'Classes/Controller/DceModuleController.php',
	'tx_dce_cache' => $extensionPath . 'Classes/Cache.php',
	'tx_dce_utility_fluidtemplate' => $extensionPath . 'Classes/Utility/FluidTemplate.php',
	'tx_dce_viewhelpers_format_rawviewhelper' => $extensionPath . 'Classes/ViewHelpers/Format/RawViewHelper.php',
	'tx_dce_viewhelpers_format_tinyviewhelper' => $extensionPath . 'Classes/ViewHelpers/Format/TinyViewHelper.php',
	'tx_dce_viewhelpers_format_addcslashesviewhelper' => $extensionPath . 'Classes/ViewHelpers/Format/AddcslashesViewHelper.php',
	'tx_dce_viewhelpers_format_wrapwithcurlybracesviewhelper' => $extensionPath . 'Classes/ViewHelpers/Format/WrapWithCurlyBracesViewHelper.php',
	'tx_dce_viewhelpers_include_resourceviewhelper' => $extensionPath . 'Classes/ViewHelpers/Include/ResourceViewHelper.php',
);
?>