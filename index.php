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

$sProcDir = "./proc";
$sSourceDir = "./proc/source";
$sDestinationDir = "./proc/destination";

$oBungler = new AS3Obfuscator($sProcDir, $sSourceDir, $sDestinationDir);
$oBunglerResult = $oBungler->obfuscate();

print_r($oBunglerResult);

exit;