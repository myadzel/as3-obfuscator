<?php

class AS3Validator {
	
	private static $aReservedWords = array(
		"abstract", "as", 
		"boolean", "break", "byte", 
		"case", "cast", "catch", "char", "class", "const", "continue", 
		"debugger", "default", "delete", "do", "double", "dynamic", 
		"each", "else", "enum", "export", "extends", 
		"false", "final", "finally", "float", "for", "function", 
		"get", "goto", 
		"if", "implements", "import", "in", "include", "instanceof", "interface", "internal", "intrinsic", "is", 
		"long", 
		"namespace", "native", "native", "new", "null", 
		"override", 
		"package", "private", "protected", "prototype", "public", 
		"return", 
		"set", "short", "static", "super", "switch", "synchronized", 
		"this", "throw", "throws", "to", "transient", "true", "try", "type", "typeof", 
		"use", 
		"var", "virtual", "void", "volatile", 
		"while", "with"
	);

	public static function getReservedWords() {
		return self::$aReservedWords;
	}
	
	public static function isReservedWord($s) {
		return in_array($s, self::$aReservedWords);
	}

	public static function isValidArgumentString($s) {
		return preg_match('@^(")?[_a-zA-Z][_a-zA-Z0-9]+(")?$@', $s);
	}
	
	public static function isASFileName($s) {
		return strtolower(substr($s, -3)) == ".as";
	}
	
}