<?php

set_time_limit(0);

//TODO: parsing bug in JSONDecoder::parseValue()

header("Content-Type: text/plain; charset=utf-8");

function __autoload($sClassName) {
	$sClassPath = realpath(__DIR__)."/classes/".str_replace("_", "/", $sClassName).".php";
	if (file_exists($sClassPath)) {
		require_once $sClassPath;
	}
}

function getReplacementAsCSV($aReplacements) {
	$aResult = array();
	foreach ($aReplacements as $sKey => $sValue) {
		array_push($aResult, "$sKey;$sValue");
	}
	
	return join("\n", $aResult);
}

$sDirectorySource = "./as3-src";
$sDirectoryDestination = "./as3-dst";

//parse directory
AS3Scanner::scanDirectory($sDirectorySource);

//obtain code stack
$aStack = AS3Parser::getStack();

//print_r($aStack);
//exit;

$bModifyClasses = true;

$aIgnoreWords = array_merge(AS3Validator::getReservedWords(), array("Main"));
$aAcceptWords = array();

foreach ($aStack as $aPackages) {
	foreach ($aPackages as $sClassName => $aClassMethods) {
		if ($bModifyClasses) {
			array_push($aAcceptWords, $sClassName);
		} else {
			//ignore class and constructor method
			array_push($aIgnoreWords, $sClassName);
		}
		//add class methods
		foreach ($aClassMethods as $sClassMethodName) {
			array_push($aAcceptWords, $sClassMethodName);
		}
	}
}

$aAcceptWords = array_unique(array_diff($aAcceptWords, $aIgnoreWords));
asort($aAcceptWords);

//build replacements table
$aReplacements = array();
foreach ($aAcceptWords as $sValue) {
	$aReplacements[$sValue] = AS3Replacer::getMD5Name($sValue);
}

AS3Replacer::setReplacements($aReplacements);

FileWriter::write("$sDirectoryDestination/repl.txt", getReplacementAsCSV($aReplacements));

AS3Replacer::replaceAll($sDirectorySource, $sDirectoryDestination);

print_r($aStack);

exit;