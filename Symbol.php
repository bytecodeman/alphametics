<?php

class Symbol {
	private $symType;
	private $symText;

	public function __construct($symType, $symText) {
		$this->symType = $symType;
		$this->symText = $symText;
	}
	
	public function getSymType() {
		return $this->symType;
	}

	public function getSymText() {
		return $this->symText;
	}

	public function __toString() {
		return "Symbol [" . $this->symText . "]";
	}

}
