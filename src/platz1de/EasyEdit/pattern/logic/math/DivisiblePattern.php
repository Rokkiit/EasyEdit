<?php

namespace platz1de\EasyEdit\pattern\logic\math;

use platz1de\EasyEdit\pattern\ParseError;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\utils\SafeSubChunkIteratorManager;

class DivisiblePattern extends Pattern
{
	/**
	 * @param int                         $x
	 * @param int                         $y
	 * @param int                         $z
	 * @param SafeSubChunkIteratorManager $iterator
	 * @param Selection                   $selection
	 * @return bool
	 */
	public function isValidAt(int $x, int $y, int $z, SafeSubChunkIteratorManager $iterator, Selection $selection): bool
	{
		if (abs($x) % $this->args[0] !== 0 && in_array("x", $this->args, true)) {
			return false;
		}
		if (abs($y) % $this->args[0] !== 0 && in_array("y", $this->args, true)) {
			return false;
		}
		if (abs($z) % $this->args[0] !== 0 && in_array("z", $this->args, true)) {
			return false;
		}
		return true;
	}

	public function check(): void
	{
		if (!is_numeric($this->args[0] ?? "")) {
			throw new ParseError("Divisible needs an int as first Argument, " . ($this->args[0] ?? "") . " given");
		}
	}
}