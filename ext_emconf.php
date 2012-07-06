<?php

########################################################################
# Extension Manager/Repository config file for ext "dce".
#
# Auto generated 06-07-2012 14:17
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Dynamic Content Elements (DCE)',
	'description' => 'Creates easily and fast dynamic content elements. It is an alternative to flexible content elements (FCE) but without need of TemplaVoila (TV). Based on Extbase and Fluid.',
	'category' => 'Backend',
	'author' => 'Armin Ruediger Vieweg',
	'author_email' => 'armin@v.ieweg.de',
	'author_company' => '',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '0.3.5',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:79:{s:16:"ext_autoload.php";s:4:"f843";s:21:"ext_conf_template.txt";s:4:"047f";s:12:"ext_icon.gif";s:4:"1139";s:17:"ext_localconf.php";s:4:"72df";s:14:"ext_tables.php";s:4:"821f";s:14:"ext_tables.sql";s:4:"4256";s:24:"ext_typoscript_setup.txt";s:4:"cbd6";s:10:"readme.txt";s:4:"83a8";s:17:"Classes/Cache.php";s:4:"aac2";s:36:"Classes/Controller/DceController.php";s:4:"3247";s:28:"Classes/Domain/Model/Dce.php";s:4:"28e1";s:33:"Classes/Domain/Model/DceField.php";s:4:"e810";s:48:"Classes/Domain/Repository/DceFieldRepository.php";s:4:"2eb5";s:43:"Classes/Domain/Repository/DceRepository.php";s:4:"024d";s:40:"Classes/Hooks/class.ux_t3lib_tcemain.php";s:4:"b727";s:44:"Classes/Hooks/tx_renderDceContentElement.php";s:4:"5f1e";s:28:"Classes/Hooks/tx_saveDce.php";s:4:"5684";s:42:"Classes/Hooks/tx_update_contentelement.php";s:4:"934c";s:53:"Classes/UserFunction/class.tx_dce_codemirrorField.php";s:4:"7234";s:57:"Classes/UserFunction/class.tx_dce_dceFieldCustomLabel.php";s:4:"6ec8";s:62:"Classes/UserFunction/class.tx_dce_formevals_lowerCamelCase.php";s:4:"0e55";s:33:"Classes/Utility/FluidTemplate.php";s:4:"1fbd";s:47:"Classes/ViewHelpers/ArrayGetIndexViewHelper.php";s:4:"9764";s:41:"Classes/ViewHelpers/ExplodeViewHelper.php";s:4:"122d";s:39:"Classes/ViewHelpers/ImageViewHelper.php";s:4:"30ae";s:52:"Classes/ViewHelpers/Format/AddcslashesViewHelper.php";s:4:"18d9";s:44:"Classes/ViewHelpers/Format/RawViewHelper.php";s:4:"a829";s:45:"Classes/ViewHelpers/Format/TinyViewHelper.php";s:4:"7332";s:60:"Classes/ViewHelpers/Format/WrapWithCurlyBracesViewHelper.php";s:4:"fa20";s:50:"Classes/ViewHelpers/Include/ResourceViewHelper.php";s:4:"c07f";s:41:"Classes/ViewHelpers/Uri/DamViewHelper.php";s:4:"180f";s:43:"Classes/ViewHelpers/Uri/ImageViewHelper.php";s:4:"0c45";s:25:"Configuration/TCA/Dce.php";s:4:"7d42";s:30:"Configuration/TCA/DceField.php";s:4:"093e";s:40:"Resources/Private/Language/locallang.xml";s:4:"821d";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"3c1e";s:45:"Resources/Private/Layouts/customTcaField.html";s:4:"3562";s:38:"Resources/Private/Layouts/default.html";s:4:"4f89";s:35:"Resources/Private/Layouts/none.html";s:4:"3562";s:44:"Resources/Private/Partials/FlexFormsXML.html";s:4:"4f6c";s:70:"Resources/Private/Partials/ConfigurationEditor/AvailableTemplates.html";s:4:"e952";s:70:"Resources/Private/Partials/ConfigurationEditor/AvailableVariables.html";s:4:"dc53";s:60:"Resources/Private/Templates/DceFieldConfiguration/Index.html";s:4:"fe9f";s:53:"Resources/Private/Templates/DceSource/ext_tables.html";s:4:"ab55";s:52:"Resources/Private/Templates/DceSource/localconf.html";s:4:"f843";s:84:"Resources/Public/CodeSnippets/ConfigurationTemplates/01 input/Simple input field.xml";s:4:"b364";s:73:"Resources/Public/CodeSnippets/ConfigurationTemplates/02 text/Full RTE.xml";s:4:"ffa6";s:80:"Resources/Public/CodeSnippets/ConfigurationTemplates/02 text/Simple textarea.xml";s:4:"54db";s:81:"Resources/Public/CodeSnippets/ConfigurationTemplates/03 check/Simple checkbox.xml";s:4:"bbd2";s:92:"Resources/Public/CodeSnippets/ConfigurationTemplates/05 select/Automatical list of items.xml";s:4:"47e6";s:87:"Resources/Public/CodeSnippets/ConfigurationTemplates/05 select/Manual list of items.xml";s:4:"9eec";s:86:"Resources/Public/CodeSnippets/ConfigurationTemplates/06 group/File upload with DAM.xml";s:4:"7b20";s:77:"Resources/Public/CodeSnippets/ConfigurationTemplates/06 group/File upload.xml";s:4:"aa77";s:111:"Resources/Public/CodeSnippets/ConfigurationTemplates/06 group/Link Pages and get pages as associative array.xml";s:4:"a4fe";s:104:"Resources/Public/CodeSnippets/ConfigurationTemplates/06 group/Link internal page with suggest wizard.xml";s:4:"35a5";s:92:"Resources/Public/CodeSnippets/ConfigurationTemplates/11 miscellaneous/Display Conditions.xml";s:4:"2228";s:63:"Resources/Public/CodeSnippets/DceViewHelpers/arrayGetIndex.html";s:4:"3153";s:57:"Resources/Public/CodeSnippets/DceViewHelpers/explode.html";s:4:"66ef";s:60:"Resources/Public/CodeSnippets/DceViewHelpers/format.raw.html";s:4:"d381";s:57:"Resources/Public/CodeSnippets/DceViewHelpers/uri.dam.html";s:4:"25d8";s:58:"Resources/Public/CodeSnippets/FamousViewHelpers/count.html";s:4:"aedf";s:58:"Resources/Public/CodeSnippets/FamousViewHelpers/debug.html";s:4:"8d43";s:56:"Resources/Public/CodeSnippets/FamousViewHelpers/for.html";s:4:"559a";s:64:"Resources/Public/CodeSnippets/FamousViewHelpers/format.crop.html";s:4:"fb01";s:64:"Resources/Public/CodeSnippets/FamousViewHelpers/format.html.html";s:4:"57f6";s:65:"Resources/Public/CodeSnippets/FamousViewHelpers/format.nl2br.html";s:4:"b9b6";s:55:"Resources/Public/CodeSnippets/FamousViewHelpers/if.html";s:4:"60f8";s:58:"Resources/Public/CodeSnippets/FamousViewHelpers/image.html";s:4:"8fef";s:63:"Resources/Public/CodeSnippets/FamousViewHelpers/link.email.html";s:4:"8236";s:66:"Resources/Public/CodeSnippets/FamousViewHelpers/link.external.html";s:4:"b541";s:62:"Resources/Public/CodeSnippets/FamousViewHelpers/link.page.html";s:4:"21ad";s:38:"Resources/Public/Icons/ajax-loader.gif";s:4:"c5d6";s:50:"Resources/Public/Icons/tx_dce_domain_model_dce.gif";s:4:"905a";s:55:"Resources/Public/Icons/tx_dce_domain_model_dcefield.gif";s:4:"905a";s:63:"Resources/Public/Icons/tx_dce_domain_model_dcefield_element.gif";s:4:"3b06";s:59:"Resources/Public/Icons/tx_dce_domain_model_dcefield_tab.gif";s:4:"fcd8";s:52:"Resources/Public/Libraries/codemirror/codemirror.css";s:4:"63ef";s:62:"Resources/Public/Libraries/codemirror/codemirror_custom.min.js";s:4:"51fa";s:45:"Resources/Public/Libraries/codemirror/init.js";s:4:"0f89";}',
);

?>