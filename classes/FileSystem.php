<?php

class FileSystem {

	public static function clean($sDir) {
		self::cleanRecursive($sDir, false);
		
		touch("$sDir/.emptydir");
	}

	private static function cleanRecursive($sDir, $bRemoveDir = false) {
		$rDirectoryHandle = opendir($sDir); 
		while (false !== ($sFileName = readdir($rDirectoryHandle))) {
			$sPath = "$sDir/$sFileName";
			if ($sFileName != "." && $sFileName != "..") {
				if (is_file($sPath)) {
					unlink($sPath);
				} elseif (is_dir($sPath)) {
					self::cleanRecursive($sPath, true);
				}
			}
		}
		closedir($rDirectoryHandle);
		
		if ($bRemoveDir) {
			rmdir($sDir);
		}
	}

}
