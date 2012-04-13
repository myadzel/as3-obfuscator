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
		
		//clear strings tokens temporary (for the safe testing of pairing brackets)
		$sPackageContentTmp = $sPackageContent;
		$sPackageContentTmp = preg_replace('@"([^\\\\"]|\\\\.)*"@', '__TOKEN_STRING_DOUBLE_QUOTED__', $sPackageContentTmp);
		$sPackageContentTmp = preg_replace("@'([^\\\\']|\\\\.)*'@", '__TOKEN_STRING_SINGLE_QUOTED__', $sPackageContentTmp);

		preg_replace_callback(
			"@([_a-z0-9$\s]*)({((?>[^{}]+)|(?R))*})+@smix", 
			create_function('$aMatch', 'return AS3Parser::callbackClassThru($aMatch, AS3Parser::$sPackageName);'), 
			$sPackageContentTmp
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
		//definition with keywords and more, like "public final class AutoSaveController extends Object"
		$sClassDefinition = $aMatches[1];

		$sClassBody = $aMatches[2];
		
		//class property attributes:
		//internal (default), dynamic, final, public
		//http://help.adobe.com/en_US/as3/learn/WS5b3ccc516d4fbf351e63e3d118a9b90204-7f36.html

		$sClassName = preg_replace("@^(.*)(class|interface)\s+([_a-z0-9$]+)(.*)$@smi", "$3", $sClassDefinition);

		self::pushToStack("", $sClassName, $sPackageName);
		
		$sClassCode = $sClassDefinition."{".self::parseClass(trim($sClassBody, " {}"), $sClassName, $sPackageName)."}";
		
		return $sClassCode;
	}
	
	private static function parseClass($s, $sClassName, $sPackageName = "") {
		$sTmp = $s;

		//scan function's body
		preg_match_all("@(?:{(?:(?>[^{}]+)|(?R))*})@smix", $sTmp, $aMatchesBodies);

		//method property attributes:
		//internal (default), private, protected, public, static, [UserDefinedNamespace]
		//http://help.adobe.com/en_US/as3/learn/WS5b3ccc516d4fbf351e63e3d118a9b90204-7f36.html
		
		//scan function's all
		preg_match_all("@
			((?:\s+(?:internal|private|protected|public|static))+)? #one or more attributes
			\s+function\s+
			(?:(get|set)\s+)? #get/set statement
			([_a-z0-9$]+) #method name
			(\([^)]*\)) #method args
			(?:\s*\:\s*(\*|[_a-z0-9$]+))? #type of returned values
		@smix", $s, $aMatches); 

		if (sizeof($aMatches[0]) != sizeof($aMatchesBodies[0])) {
			throw new Exception("Can't parse class methods, miscount bodies and definitions");
		}

		for ($i = 0; $i < sizeof($aMatches[3]); $i++) {
			self::pushToStack($aMatches[3][$i], $sClassName, $sPackageName);
		}

		//TODO:
		//scan and replace args
		//scan and replace local vars
	}
	
}