<?php

class AS3Scanner {

	public static function scanDirectory($sDirectory) {
		if ($rDirectoryHandle = opendir($sDirectory)) {
			while (false !== ($sFile = readdir($rDirectoryHandle))) {
				if ($sFile != "." && $sFile != "..") {
					if (!is_dir("$sDirectory/$sFile")) {
							if (AS3Validator::isASFileName($sFile)) {
								AS3Parser::parse(FileReader::read("$sDirectory/$sFile"));
							}
					} else {
						self::scanDirectory("$sDirectory/$sFile");
					}
				}
			}
			closedir($rDirectoryHandle);
		}
	}

}