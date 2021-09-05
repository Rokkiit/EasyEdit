<?php

namespace platz1de\EasyEdit\task\selection;

use platz1de\EasyEdit\history\HistoryManager;
use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\BlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\StaticBlockListSelection;
use platz1de\EasyEdit\task\EditTask;
use platz1de\EasyEdit\task\EditTaskResult;
use platz1de\EasyEdit\task\queued\QueuedEditTask;
use platz1de\EasyEdit\task\selection\cubic\CubicStaticUndo;
use platz1de\EasyEdit\task\selection\type\PastingNotifier;
use platz1de\EasyEdit\utils\AdditionalDataManager;
use platz1de\EasyEdit\utils\SafeSubChunkExplorer;
use platz1de\EasyEdit\utils\TaskCache;
use platz1de\EasyEdit\worker\WorkerAdapter;
use pocketmine\block\tile\Tile;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;
use pocketmine\world\World;

class UndoTask extends EditTask
{
	use CubicStaticUndo;
	use PastingNotifier;

	/**
	 * @param BlockListSelection $selection
	 */
	public static function queue(BlockListSelection $selection): void
	{
		Selection::validate($selection, StaticBlockListSelection::class);
		WorkerAdapter::queue(new QueuedEditTask($selection, new Pattern([]), new Position(0, World::Y_MIN, 0, $selection->getWorld()), self::class, new AdditionalDataManager(["edit" => true]), new Vector3(0, 0, 0), static function (EditTaskResult $result): void {
			/** @var StaticBlockListSelection $redo */
			$redo = $result->getUndo();
			HistoryManager::addToFuture($redo->getPlayer(), $redo);
		}));
	}

	/**
	 * @return string
	 */
	public function getTaskName(): string
	{
		return "undo";
	}

	/**
	 * @param SafeSubChunkExplorer  $iterator
	 * @param CompoundTag[]         $tiles
	 * @param Selection             $selection
	 * @param Pattern               $pattern
	 * @param Vector3               $place
	 * @param BlockListSelection    $toUndo
	 * @param SafeSubChunkExplorer  $origin
	 * @param int                   $changed
	 * @param AdditionalDataManager $data
	 */
	public function execute(SafeSubChunkExplorer $iterator, array &$tiles, Selection $selection, Pattern $pattern, Vector3 $place, BlockListSelection $toUndo, SafeSubChunkExplorer $origin, int &$changed, AdditionalDataManager $data): void
	{
		/** @var StaticBlockListSelection $selection */
		Selection::validate($selection, StaticBlockListSelection::class);
		$selection->useOnBlocks($place, function (int $x, int $y, int $z) use ($iterator, &$tiles, $selection, $toUndo, &$changed): void {
			$block = $selection->getIterator()->getBlockAt($x, $y, $z);
			if (Selection::processBlock($block)) {
				$toUndo->addBlock($x, $y, $z, $iterator->getBlockAt($x, $y, $z));
				$iterator->setBlockAt($x, $y, $z, $block);
				$changed++;

				if (isset($tiles[World::blockHash($x, $y, $z)])) {
					$toUndo->addTile($tiles[World::blockHash($x, $y, $z)]);
					unset($tiles[World::blockHash($x, $y, $z)]);
				}
			}
		});

		/** @var StaticBlockListSelection $total */
		$total = TaskCache::getFullSelection();
		foreach ($total->getTiles() as $tile) {
			$tiles[World::blockHash($tile->getInt(Tile::TAG_X), $tile->getInt(Tile::TAG_Y), $tile->getInt(Tile::TAG_Z))] = $tile;
		}
	}
}