<?php

class Permutations {
	private $numbers;
	private $top;
	private $minValue;
	private $maxValue;
	private $unique;

	public function __construct($count, $minValue, $maxValue, $unique = TRUE) {
		$this->numbers = [];
		$this->unique = $unique;
		if ($this->unique) {
		    for ($i = 0; $i < $count; $i++)
				$this->numbers[] = $minValue + $i;
		}
		else {
		    for ($i = 0; $i < $count; $i++)
				$this->numbers[] = 0;
		}
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
		$this->top = $count - 1;
	}

	public function setNumbers($values) {
		$this->numbers = $values;
		$this->top = count($values) - 1;		
	}
	
	public function hasNext() {
		return $this->top >= 0;
	}

	public function next() {
		$retval = $this->numbers;
		
		do {
			$this->numbers[$this->top]++;
			if ($this->numbers[$this->top] > $this->maxValue) {
				$this->top--;
			} else if (!$this->numberAlreadyinArray($this->numbers[$this->top]))
				break;
		} while ($this->top >= 0);
		if ($this->top >= 0) {
			for ($this->top++; $this->top < count($this->numbers); $this->top++) {
				for ($i = $this->minValue; $i <= $this->maxValue; $i++)
					if (!$this->numberAlreadyinArray($i)) {
						$this->numbers[$this->top] = $i;
						break;
					}
			}
			if ($this->top >= count($this->numbers))
				$this->top = count($this->numbers) - 1;
		}

		return $retval;
	}

	private function numberAlreadyinArray($value) {
		if (!$this->unique)
			return false;
		for ($i = 0; $i < $this->top; $i++)
			if ($this->numbers[$i] == $value)
				return true;
		return false;
	}
	
	private function valueNoAlreadyinArray($pos, $value) {
		for ($i = 0; $i < $pos; $i++)
			if ($this->numbers[$i] == $value)
				return true;
		return false;
	}

	public function valueNo() {
		$number = 0;
		if (!$this->unique) {
			for ($i = 0; $i < count($this->numbers); $i++)
				$number = 10 * $number + $values[$i];
		}
		else {
			for ($i = 0; $i < count($this->numbers); $i++) {
				$posvalue = 1;
				for ($j = $i + 1; $j < count($this->numbers); $j++)
					$posvalue *= $this->maxValue - $this->minValue + 1 - $j;

				$count = 0;
            	for ($j = $this->minValue; $j <= $this->maxValue && $j != $this->numbers[$i]; $j++) {
					if (!$this->valueNoAlreadyinArray($i, $j))
						$count++;
				}

				$number += $count * $posvalue;
			}
		}
		return $number;
	}

	public function noOfCombinations() {
		$number = 1;
		if (!$this->unique) {
			for ($i = 0; $i < count($this->numbers); $i++)
				$number *= 10;
		}
		else {
			for ($i = 0; $i < count($this->numbers); $i++)
				$number *= $this->maxValue - $this->minValue + 1 - $i;
		}
		return $number;		
	}

}
