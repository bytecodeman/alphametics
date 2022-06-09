<?php

class AnalyzeAlphaMetics {

	private static function translate($expr, $values) {
		$expr = strtoupper($expr);
		foreach ($values as $key => $value)
		{
			$expr = str_replace($key, $value, $expr);
		}
		return $expr;
	}
	
	public static function outputSolutions($expr, $results) {
		$solutions = "";
		if (count($results) == 0)
			$solutions = "No solutions to problem";
		else
		foreach ($results as $result)
			$solutions .= AnalyzeAlphaMetics::translate($expr, $result) . "\n";
		return $solutions;
	}

	private static function generateVariableMap($variables, $numbers) {
		$values = [];
		$i = 0;
		while ($i < strlen($variables))
		{
			$values[$variables[$i]] = $numbers[$i];
			$i++;
		}
		return $values;
	}
	
	private static function scanForVariables($expr) {
		$variables = "";
		$i = 0;
		while ($i < strlen($expr))
		{
			$ch = strtoupper($expr[$i]);
			if (ctype_alpha($ch) && strpos($variables, $ch) === FALSE)
				$variables .= $ch;
			$i++;
		}
		return $variables;
	}
	
	private static function getLeadingVariables($expr) {
		$variables = "";
		$exprLex = new Lex($expr);
		do {
			$sym = $exprLex->nextSymbol();
			$type = $sym->getSymType();
			if ($type == SymbolType::EXPREND)
				break;
			if ($type == SymbolType::OPERAND) {
				$ch = strtoupper($sym->getSymText()[0]);
				if (ctype_alpha($ch)) {
					if (strpos($variables, $ch) === FALSE)
						$variables .= $ch;
				}
				else if ($ch === "0")
					throw new Exception("Operand cannot begin with 0");
			}
		} while (true);
		return $variables;
	}

	private static function leadingZero($leadingVars, $values) {
		for ($i = 0; $i < strlen($leadingVars); $i++) {
			$ch = $leadingVars[$i];
			if ($values[$ch] == 0)
				return true;
		}
		return false;
	}

	public static function getAllAlphameticSolutions($expr, $min, $max, $unique, $leadingZero) {
		$parser = new Parser($expr);
		$variables = AnalyzeAlphaMetics::scanForVariables($expr);
		if ($unique && strlen($variables) > 10)
			throw new Exception("Cannot have more than 10 variables when uniqueness is specified");
		$leadingVars = $leadingZero ? "" : AnalyzeAlphaMetics::getLeadingVariables($expr);

		$results = [];
		$perm = new Permutations(strlen($variables), $min, $max, $unique);
		while ($perm->hasNext()) {
			$rawValues = $perm->next();
			$values = AnalyzeAlphaMetics::generateVariableMap($variables, $rawValues);	
			if (!AnalyzeAlphaMetics::leadingZero($leadingVars, $values)) {
				$result = $parser->evaluate($values);
				if ($result != null) {
					$results[] = $result;
				}
			}
		}
		return $results;
	}

}
