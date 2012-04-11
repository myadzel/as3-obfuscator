<?php

class AS3Cleaner {

	public static function clean($f) {
		$f = self::cleanBOM($f);
		
		//remove BOM
		$f = preg_replace("@^\xEF\xBB\xBF@", "", $f);
	
		//new line fix
		$f = preg_replace("@\r@", "\n", $f);

		//remove /**/ comments
		$f = preg_replace("@/\*.*?\*/@s", "", $f);
		$f = preg_replace("@\n\s*\n@", "\n", $f);
		
		//remove // comments
		$f = preg_replace("@//[^\r\n]+@", "", $f);

		//remove tab
		$f = preg_replace("@\t@", "    ", $f);

		//remove double lines
		$f = preg_replace("@\n+@", "\n", $f);

		//remove double space
		$f = preg_replace("@[ ]+@", " ", $f);
		
		//remove space on new after line
		$f = preg_replace("@\n +@", "\n", $f);
		
		return trim($f);
	}
	
	public static function cleanBOM($f) {
		//remove UTF-8 byte order mark
		return preg_replace("@^\xEF\xBB\xBF@", "", $f);
	}

}