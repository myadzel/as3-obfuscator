<?php

class AS3Cleaner {

	public static function clean($s) {
		//remove BOM
		$s = self::cleanBOM($s);
	
		//new line fix
		$s = preg_replace("@\r@", "\n", $s);

		//TODO: bugs in simple regexp, use another cutting method
		//remove /**/ comments
		$s = preg_replace("@/\*.*?\*/@s", "", $s);
		$s = preg_replace("@\n\s*\n@", "\n", $s);
		
		//remove // comments
		$s = preg_replace("@//[^\r\n]+@", "", $s);

		//remove tab
		$s = preg_replace("@\t@", "    ", $s);

		//remove double lines
		$s = preg_replace("@\n+@", "\n", $s);

		//remove double space
		$s = preg_replace("@[ ]+@", " ", $s);
		
		//remove space on new after line
		$s = preg_replace("@\n +@", "\n", $s);
		
		return trim($s);
	}
	
	public static function cleanBOM($s) {
		//remove UTF-8 byte order mark
		if (substr($s, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
			$s = substr($s, 3);
		}
		
		return $s;
	}

}