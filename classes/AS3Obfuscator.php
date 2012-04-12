<?php

class AS3Obfuscator {

	private $sProcDir;
	
	private $sSourceDir;
	private $sDestinationDir;
	
	private $aStack = array();
	private $aReplacements = array();
	
	public function __construct($sProcDir, $sSourceDir, $sDestinationDir) {
		$this->sProcDir = $sProcDir;
		
		$this->sSourceDir = $sSourceDir;
		$this->sDestinationDir = $sDestinationDir;

		//scan directory
		AS3Scanner::scanDirectory($sSourceDir);
		
		//obtain code stack
		$this->aStack = AS3Parser::getStack();

		//build table for replace, set process directory
		AS3Replacer::buildReplacements($this->aStack, $this->sProcDir);
		
		//get replacements array
		$this->aReplacements = AS3Replacer::getReplacements();
	}

	public function obfuscate() {
		AS3Replacer::proceed($this->sSourceDir, $this->sDestinationDir);

		return array(
			"stack" => $this->getStack(),
			"replacements" => $this->getReplacements()
		);
	}
	
	public function getStack() {
		return $this->aStack;
	}
	
	public function getReplacements() {
		return $this->aReplacements;
	}

}
