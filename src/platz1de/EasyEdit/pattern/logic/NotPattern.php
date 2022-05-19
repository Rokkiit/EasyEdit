<?php

namespace platz1de\EasyEdit\pattern\logic;

use platz1de\EasyEdit\pattern\parser\WrongPatternUsageException;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\utils\ExtendedBinaryStream;
use platz1de\EasyEdit\world\ChunkController;

class NotPattern extends Pattern
{
	/**
	 * @param Pattern $piece
	 */
	public function __construct(Pattern $piece)
	{
		parent::__construct([$piece]);
		if (count($this->pieces) !== 1) {
			throw new WrongPatternUsageException("Not needs exactly one child pattern");
		}
	}

	/**
	 * @param int             $x
	 * @param int             $y
	 * @param int             $z
	 * @param ChunkController $iterator
	 * @param Selection       $current
	 * @param Selection       $total
	 * @return int
	 */
	public function getFor(int $x, int &$y, int $z, ChunkController $iterator, Selection $current, Selection $total): int
	{
		return $this->pieces[0]->getFor($x, $y, $z, $iterator, $current, $total);
	}

	/**
	 * @param int             $x
	 * @param int             $y
	 * @param int             $z
	 * @param ChunkController $iterator
	 * @param Selection       $current
	 * @param Selection       $total
	 * @return bool
	 */
	public function isValidAt(int $x, int $y, int $z, ChunkController $iterator, Selection $current, Selection $total): bool
	{
		return !$this->pieces[0]->isValidAt($x, $y, $z, $iterator, $current, $total);
	}

	/**
	 * @param ExtendedBinaryStream $stream
	 * @return void
	 */
	public function putData(ExtendedBinaryStream $stream): void { }

	/**
	 * @param ExtendedBinaryStream $stream
	 * @return void
	 */
	public function parseData(ExtendedBinaryStream $stream): void { }
}