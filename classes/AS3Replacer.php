<?php

class AS3Replacer {

	private static $aReplacements = array();
	
	private static $aStack = array();
	private static $sProcDir = "";

	private static $sIgnoredWordsFileName = "ignored-words.txt";
	private static $bModifyClassNames = true;

	private static $aIgnoredWords = array();
	private static $aAcceptedWords = array();

	public static function getReplacements() {
		return self::$aReplacements;
	}
	
	public static function getRandomAlnumName() {
		return uniqid("__");
	}
	
	public static function replace($sCode) {
		foreach (self::$aReplacements as $sKey => $sValue) {
			$sCode = preg_replace("@([^_a-zA-Z0-9])($sKey)([^_a-zA-Z0-9])@sm", "$1$sValue$3", $sCode);
		}
		
		return $sCode;
	}

	public static function buildReplacements($aStack, $sProcDir) {
		self::$aStack = $aStack;
		self::$sProcDir = $sProcDir;
		
		self::collectIgnoredWords();
		self::collectAcceptedWords();

		//build replacements table
		$aReplacements = array();
		foreach (self::$aAcceptedWords as $sValue) {
			$aReplacements[$sValue] = self::getRandomAlnumName();
		}

		self::$aReplacements = $aReplacements;
	}

	public static function proceed($sSourceDir, $sDestinationDir) {
		//clean old result
		FileSystem::clean($sDestinationDir);
		
		self::proceedDir($sSourceDir, $sDestinationDir);
	}
	
	private static function proceedDir($sSourceDir, $sDestinationDir) {
		$rDirectoryHandle = opendir($sSourceDir); 
		while (false !== ($sFileName = readdir($rDirectoryHandle))) {
			if ($sFileName != "." && $sFileName != "..") {
				if (!is_dir("$sSourceDir/$sFileName")) {
						if (AS3Validator::isASFileName($sFileName)) {
							//read and clean (don't remove comments, etc.)
							$sFileContents = FileReader::read("$sSourceDir/$sFileName");
							$sFileContents = AS3Cleaner::cleanBOM($sFileContents);

							$sNewFileContents = self::replace($sFileContents);

							//get filename from replacement table
							$sNewFileName = "";
							$sFileNameKey = substr($sFileName, 0, -3);
							if (array_key_exists($sFileNameKey, self::$aReplacements)) {
								$sNewFileName = self::$aReplacements[substr($sFileName, 0, -3)];
							}

							if (trim($sNewFileName) != "") {
								FileWriter::write("$sDestinationDir/$sNewFileName.as", $sNewFileContents);
							} else {
								FileWriter::write("$sDestinationDir/$sFileName", $sNewFileContents);
							}
						}
				} else {
					self::proceedDir("$sSourceDir/$sFileName", "$sDestinationDir/$sFileName");
				}
			}
		}
		closedir($rDirectoryHandle);
	}

	private static function collectIgnoredWords() {
		$aIgnoredWords = array();
		
		$sFileContents = FileReader::read(rtrim(self::$sProcDir, "/")."/".self::$sIgnoredWordsFileName);
		$aLines = preg_split("@[\n\r]@", $sFileContents);

		if (sizeof($aLines)) {
			foreach ($aLines as $sLine) {
				$sLine = trim($sLine);
				if (substr($sLine, 0, 1) != "#" && $sLine != "") {
					array_push($aIgnoredWords, $sLine);
				}
			}
		}
		
		//merge with reserved words
		self::$aIgnoredWords = array_merge(AS3Validator::getReservedWords(), $aIgnoredWords);
	}
	
	private static function collectAcceptedWords() {
		$aAcceptedWords = array();
		
		foreach (self::$aStack as $aPackages) {
			foreach ($aPackages as $sClassName => $aClassMethods) {
				if (self::$bModifyClassNames) {
					array_push($aAcceptedWords, $sClassName);
				} else {
					//ignore class and constructor
					array_push(self::$aIgnoredWords, $sClassName);
				}
				//add class methods
				foreach ($aClassMethods as $sClassMethodName) {
					array_push($aAcceptedWords, $sClassMethodName);
				}
			}
		}
		
		$aAcceptedWords = array_unique(array_diff($aAcceptedWords, self::$aIgnoredWords));
		asort($aAcceptedWords);
		
		//set static 
		self::$aAcceptedWords = $aAcceptedWords;
	}

}