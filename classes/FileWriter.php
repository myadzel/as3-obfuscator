<?php

class FileWriter {

	private static function makeDirectoryRecursive($sFilePath, $iMode = 0775) {
		if (!is_dir(dirname($sFilePath))) {
			self::makeDirectoryRecursive(dirname($sFilePath), $iMode);
		}

		if (!is_dir($sFilePath)) {
			// 3-rd argument for recursive mkdir, PHP5
			mkdir($sFilePath, $iMode, true);
			chmod($sFilePath, $iMode);
		}
	}

	public static function write($sFileName, $sContents = "") {
		self::makeDirectoryRecursive(dirname($sFileName));

		return file_put_contents($sFileName, $sContents);
	}

}