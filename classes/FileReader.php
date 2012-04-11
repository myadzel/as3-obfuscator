<?php

class FileReader {

	public static function read($sFileName) {
		return file_get_contents($sFileName);
	}

}
