<?php

class AS3Replacer {

	private static $aReplacements = array();

	public static function setReplacements($aReplacements) {
		self::$aReplacements = $aReplacements;
	}
	
	public static function getRandomAlphaName($iLength = 10, $sPrefix = "__") {
		return $sPrefix.substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", 10)), 0, $iLength);
	}
	
	public static function getMD5Name($sValue, $sPrefix = "__") {
		return $sPrefix.md5($sValue);
	}
	
	public static function replace($sCode) {
		foreach(self::$aReplacements as $sKey => $sValue) {
			$sCode = preg_replace("@([^_a-zA-Z0-9])($sKey)([^_a-zA-Z0-9])@sm", "$1$sValue$3", $sCode);
		}

		return $sCode;
	}

	public static function replaceAll($sDirectory, $sDirectoryDestination) {
		if ($rDirectoryHandle = opendir($sDirectory)) {
			while (false !== ($sFileName = readdir($rDirectoryHandle))) {
				if ($sFileName != "." && $sFileName != "..") {
					if (!is_dir("$sDirectory/$sFileName")) {
							if (AS3Validator::isASFileName($sFileName)) {
								//read and clean (don't remove comments, etc.)
								$sFileContents = FileReader::read("$sDirectory/$sFileName");
								$sFileContents = AS3Cleaner::cleanBOM($sFileContents);
								
								$sNewFileContents = self::replace($sFileContents);

								//get filename from replacement table
								$sNewFileName = "";
								$sFileNameKey = substr($sFileName, 0, -3);
								if (array_key_exists($sFileNameKey, self::$aReplacements)) {
									$sNewFileName = self::$aReplacements[substr($sFileName, 0, -3)];
								}

								if (trim($sNewFileName) != "") {
									FileWriter::write("$sDirectoryDestination/$sNewFileName.as", $sNewFileContents);
								} else {
									FileWriter::write("$sDirectoryDestination/$sFileName", $sNewFileContents);
								}
							}
					} else {
						self::replaceAll("$sDirectory/$sFileName", "$sDirectoryDestination/$sFileName");
					}
				}
			}
			closedir($rDirectoryHandle);
		}
	}

}