<?php

class AS3Parser {

	//original code
	private static $sAS3CodeOriginal = "";
	
	//3d array of package/class/function
	private static $aStack = array();
	
	//for preg_replace_callback usage only
	static $sPackageName = "";

	public static function parse($sAS3Code) {
		self::$sAS3CodeOriginal = $sAS3Code;
		
		$sAS3Code = AS3Cleaner::clean($sAS3Code);

		return preg_replace_callback("@^([^{]+)({)(.+)(})([^{]*)$@sm", "self::callbackPackage", $sAS3Code, 1);
	}
	
	public static function getStack() {
		return self::$aStack;
	}

	private static function pushToStack($sFunctionDefinition = "", $sClassName, $sPackageName) {
		preg_match("@([_a-z0-9$]+)\s*$@smi", $sFunctionDefinition, $aMatches);

		//TODO: function definition can contain prefix flag get/set (function names are identical)
		$sFunctionName = isset($aMatches[1]) && $sFunctionDefinition ? $aMatches[1] : "";
		
		if (!in_array($sFunctionName, self::$aStack) && !empty($sFunctionName)) {
			self::$aStack[$sPackageName][$sClassName][] = $sFunctionName;
		} else {
			//class without functions
			self::$aStack[$sPackageName][$sClassName] = array();
		}
	}
	
	private static function callbackPackage($aMatches) {
		$sPackageName = $aMatches[1];
		$sPackageName = preg_replace("@^(.*)(package)\s*([_a-z0-9.$]+)(.*)$@smi", "$3", $sPackageName);

		$sPackageContent = $aMatches[3];

		//set package name
		self::$sPackageName = $sPackageName;

		$sPackageContent = preg_replace_callback(
			"@([_a-z0-9$\s]*)({((?>[^{}]+)|(?R))*})+@smix", 
			create_function('$aMatch', 'return AS3Parser::callbackClassThru($aMatch, AS3Parser::$sPackageName);'), 
			$sPackageContent
		);
		
		//restore (reset) package name
		self::$sPackageName = "";

		return $aMatches[1].$aMatches[2].$sPackageContent.$aMatches[4].$aMatches[5];
	}
	
	public static function callbackClassThru($aMatches, $sPackageName) {
		if (preg_match("/class\s+/i", $aMatches[1])) {
			return self::callbackClass($aMatches, $sPackageName);
		} else {
			return $aMatches[0];
		}
	}
	
	//private methods
	private static function callbackClass($aMatches, $sPackageName) {
		$sClassDefinition = $aMatches[1];

		$sClassBody = $aMatches[2];

		$sClassName = preg_replace("@^(.*)(class|interface)\s+([_a-z0-9$]+)(.*)$@smi", "$3", $sClassDefinition);

		self::pushToStack("", $sClassName, $sPackageName);
		
		$sClassCode = $sClassDefinition."{".self::parseClass(trim($sClassBody, " {}"), $sClassName, $sPackageName)."}";
		
		return $sClassCode;
	}
	
	private static function parseClass($s, $sClassName, $sPackageName = "") {
		$sResult = $s;

		/*
		//scan function's body
		preg_match_all("@({((?>[^{}]+)|(?R))*})+@smi", $sResult, $aMatches);  
		*/

		//search for functions
		preg_match_all("@(((?:private|public|protected)\s+)?function[\s_a-z][\s_a-z0-9]+)(\([^)]*\))(\s*\:\s*(\*|[\s_a-z][\s_a-z0-9]+))?@smi", $s, $aMatches); 

		for ($i = 0; $i < sizeof($aMatches[1]); $i++) {
			self::pushToStack($aMatches[1][$i], $sClassName, $sPackageName);
		}

		//TODO:
		//scan and replace args
		//scan and replace local vars
	}
	
}