<?php

namespace platz1de\EasyEdit\thread\output;

use platz1de\EasyEdit\task\benchmark\BenchmarkManager;
use platz1de\EasyEdit\utils\ExtendedBinaryStream;

class BenchmarkCallbackData extends OutputData
{
	private string $world;
	/**
	 * @var array<array{string, float, int}>
	 */
	private array $result;

	/**
	 * @param string                           $world
	 * @param array<array{string, float, int}> $result
	 */
	public static function from(string $world, array $result): void
	{
		$data = new self();
		$data->world = $world;
		$data->result = $result;
		$data->send();
	}

	public function handle(): void
	{
		BenchmarkManager::benchmarkCallback($this->world, $this->result);
	}

	public function putData(ExtendedBinaryStream $stream): void
	{
		$stream->putString($this->world);

		$stream->putInt(count($this->result));
		foreach ($this->result as $result) {
			$stream->putString($result[0]);
			$stream->putFloat($result[1]);
			$stream->putInt($result[2]);
		}
	}

	public function parseData(ExtendedBinaryStream $stream): void
	{
		$this->world = $stream->getString();

		$count = $stream->getInt();
		$this->result = [];
		for ($i = 0; $i < $count; $i++) {
			$this->result[] = [$stream->getString(), $stream->getFloat(), $stream->getInt()];
		}
	}
}