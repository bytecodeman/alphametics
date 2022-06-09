<?php

class Lex {
	private $strExpr;
	private $ptr;
	
	public function __construct($strExpr) {
		$this->strExpr = strtoupper($strExpr);
		$this->ptr = 0;
	}
	
	private function skipWhiteSpace() {
		while ($this->ptr < strlen($this->strExpr) && ctype_space($this->strExpr[$this->ptr])) {
			$this->ptr++;
		}
	}
	
	private function getOperand() {
		$primary = "";
		while ($this->ptr < strlen($this->strExpr) && 
	          (ctype_alpha($this->strExpr[$this->ptr]) || ctype_digit($this->strExpr[$this->ptr]))) {
			$primary .= $this->strExpr[$this->ptr];
			$this->ptr++;
		}
		return $primary;
	}
	
	public function nextSymbol() {
		$this->skipWhiteSpace();
		if ($this->ptr >= strlen($this->strExpr))
			return new Symbol(SymbolType::EXPREND, "EXPREND");
		$ch = $this->strExpr[$this->ptr];
		if (ctype_alpha($this->strExpr[$this->ptr]) || ctype_digit($this->strExpr[$this->ptr])) {
			$primary = $this->getOperand();
			$sym =  new Symbol(SymbolType::OPERAND, $primary);
			return $sym;
		}
		$this->ptr++;
		switch ($ch) {
		case '(':
			return new Symbol(SymbolType::LPAREN, "(");
		case ')':
			return new Symbol(SymbolType::RPAREN, ")");
		case '*':
			return new Symbol(SymbolType::TIMES, "*");
		case '/':
			return new Symbol(SymbolType::SLASH, "/");
		case '%':
			return new Symbol(SymbolType::MOD, "%");
		case '+':
			return new Symbol(SymbolType::PLUS, "+");
		case '-':
			return new Symbol(SymbolType::MINUS, "-");
		case '=':
			return new Symbol(SymbolType::EQUALS, "=");
		}
		return new Symbol(SymbolType::ERROR, $ch);
	}
	
}
