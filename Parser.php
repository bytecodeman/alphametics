<?php

class Parser {
	private $expr;
	private $lex;
	private $values;
	private $currSym;
	private $prevSym;

	public function __construct($expr) {
		$this->expr = $expr;
	}

	private function nextSym() {
		$this->prevSym = $this->currSym;
		$this->currSym = $this->lex->nextSymbol();
	}

	private function accept($symType) {
		if ($this->currSym->getSymType() == $symType)
		{
			$this->nextSym();
			return true;
		}
		return false;
	}

	private function expect($s) {
		if (!$this->accept($s->getSymType()))
			throw new Exception("Expected: " . $s . " Found: " . $this->currSym);
	}

	private function getOperandValue($symText) {
		$value = 0;
		for ($i = 0; $i < strlen($symText); $i++)
		{
			$ch = $symText[$i];
			$value = 10 * $value + (ctype_digit($ch) ? ord($ch) - ord('0') : $this->values[$ch]);
		}
		return $value;
	}

	private function factor() {
		if ($this->accept(SymbolType::OPERAND))
			return $this->getOperandValue($this->prevSym->getSymText());
		if ($this->accept(SymbolType::LPAREN))
		{
			$expr = $this->expression();
			$this->expect(new Symbol(SymbolType::RPAREN, ")"));
			return $expr;
		}
		throw new Exception("Factor: syntax error");
	}

	private function term() {
		$prod = $this->factor();
		while ($this->currSym->getSymType() == SymbolType::TIMES || 
			   $this->currSym->getSymType() == SymbolType::SLASH ||
			   $this->currSym->getSymType() == SymbolType::MOD)
			   
		{
			$op = $this->currSym->getSymType();
			$this->nextSym();
			$fact = $this->factor();
			if ($op == SymbolType::TIMES)
				$prod *= $fact;
			elseif ($op == SymbolType::SLASH)
				if ($fact == 0)
					if ($prod < 0)
						$prod = PHP_INT_MIN;
				    elseif ($prod == 0)
						$prod = 0;
				    else
						$prod = PHP_INT_MAX;
				else
					$prod = (int)($prod / $fact);
			else 
				$prod %= $fact;
		}
		return $prod;
	}

	private function expression() {
		$sum = 0;
		$sign = 1;
		if ($this->currSym->getSymType() == SymbolType::PLUS || $this->currSym->getSymType() == SymbolType::MINUS)
		{
			$sign = $this->currSym->getSymType() == SymbolType::PLUS ? 1 : -1;
			$this->nextSym();
		}
		$sum += $sign * $this->term();
		while ($this->currSym->getSymType() == SymbolType::PLUS || $this->currSym->getSymType() == SymbolType::MINUS)
		{
			$sign = $this->currSym->getSymType() == SymbolType::PLUS ? 1 : -1;
			$this->nextSym();
			$sum += $sign * $this->term();
		}
		return $sum;
	}

	private function equation() {
		$lhs = $this->expression();
		$this->expect(new Symbol(SymbolType::EQUALS, "="));
		$rhs = $this->expression();
		$this->expect(new Symbol(SymbolType::EXPREND, ""));
		return $lhs == $rhs;
	}

	public function evaluate($input) {
		$this->lex = new Lex($this->expr);
		$this->values = $input;
		$this->nextSym();
		$equationResult = $this->equation();
		$this->expect(new Symbol(SymbolType::EXPREND, ""));
		return $equationResult ? $input : null;
	}

}
