<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Inventory;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\EnchantUtil;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolEnhanceUtil;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class LevelToolEnhanceInventory extends InvMenu
{
	private const SLOT_ACCEPT_BUTTON = 49;
	private const SLOT_ACCEPT_BUTTON1 = 48;
	private const SLOT_ACCEPT_BUTTON2 = 50;
	private const SLOT_INPUT_ITEM = 20;
	private const SLOT_INPUT_TICKET = 24;

	private const MAX_LEVELS = [
		0 => 8, // 효율 최대레벨
		1 => 6, // 견고 최대레벨
		2 => 6 // 희귀품 채굴 최대레벨
	];

	private const CHANCES = [
		0 => 90, // 0 -> 1 성공확률
		1 => 70, // 1 -> 2 성공확률
		2 => 60, // 2 -> 3 성공확률
		3 => 50, // 3 -> 4 성공확률
		4 => 40, // 4 -> 5 성공확률
		5 => 5, // 5 -> 6 성공확률
		6 => 3, // 6 -> 7 성공확률
		7 => 1, // 7 -> 8 성공확률
	];

	public function __construct()
	{
		parent::__construct(InvMenuHandler::getTypeRegistry()->get(InvMenuTypeIds::TYPE_DOUBLE_CHEST));
		$this->setName("§1");
		$contents = array_fill(0, 54, VanillaItems::FEATHER()->setCustomName("§r"));
		$contents[self::SLOT_ACCEPT_BUTTON] = VanillaItems::FEATHER()->setCustomName(implode("§r\n", [
			"§l강화하기",
			"<- 강화할 아이템 | 도구강화 주문서 ->"
		]));
		$contents[self::SLOT_ACCEPT_BUTTON1] = VanillaItems::FEATHER()->setCustomName(implode("§r\n", [
			"§l강화하기",
			"<- 강화할 아이템 | 도구강화 주문서 ->"
		]));
		$contents[self::SLOT_ACCEPT_BUTTON2] = VanillaItems::FEATHER()->setCustomName(implode("§r\n", [
			"§l강화하기",
			"<- 강화할 아이템 | 도구강화 주문서 ->"
		]));
		$contents[self::SLOT_INPUT_ITEM] = VanillaItems::AIR();
		$contents[self::SLOT_INPUT_TICKET] = VanillaItems::AIR();
		$this->getInventory()->setContents($contents);
	}

	public function handleInventoryTransaction(Player $player, Item $out, Item $in, SlotChangeAction $action, InventoryTransaction $transaction): InvMenuTransactionResult
	{
		$slot = $action->getSlot();
		if($slot === self::SLOT_INPUT_ITEM || $slot === self::SLOT_INPUT_TICKET) return new InvMenuTransactionResult(false);
		if($slot === self::SLOT_ACCEPT_BUTTON) {
			$inventory = $action->getInventory();
			$target = $inventory->getItem(self::SLOT_INPUT_ITEM);
			$ticket = $inventory->getItem(self::SLOT_INPUT_TICKET);
			if(!$target instanceof Tool || !ToolEnhanceUtil::isTicket($ticket)) {
				$player->sendMessage(ToolCore::Prefix."도구와 티켓을 똑바로 넣어준 후 다시 시도해주세요");
			} else if(LevelToolUtil::isLevelTool($target)) {
				$player->sendMessage(ToolCore::Prefix."레벨도구에는 강화를 할 수 없습니다");
			} else if($ticket->getCount() !== 1) {
				$player->sendMessage(ToolCore::Prefix."강화권은 한장만 넣은 후 다시 시도해주세요");
			} else {
				$enchant = ToolEnhanceUtil::getEnchantment($ticket);
				$enchantIndex = match ($enchant) {
					VanillaEnchantments::EFFICIENCY() => 0,
					VanillaEnchantments::UNBREAKING() => 1,
					EnchantUtil::$fortune => 2,
					default => null
				};
				if($enchantIndex === null) {
					$player->sendMessage(ToolCore::Prefix."알 수 없는 오류입니다. 방법을 제보해주세요!");
				} else {
					$beforeLevel = $target->getEnchantmentLevel($enchant);
					$maxLevel = self::MAX_LEVELS[$enchantIndex];
					$rand = rand(1, 100);
					$chance = self::CHANCES[$enchantIndex];
					if($beforeLevel >= $maxLevel) {
						$player->sendMessage(ToolCore::Prefix."해당 강화는 최대 레벨을 도달하였습니다");
					} else if($chance >= $rand) {
						$this->getInventory()->removeItem($target);
						$this->getInventory()->removeItem($ticket);
						$player->getInventory()->addItem(EnchantUtil::updateEnchantment($target, $enchant, $beforeLevel + 1, $maxLevel));
						$player->sendMessage(ToolCore::Prefix."축하합니다 강화에 성공했습니다 !");
					} else {
						$this->getInventory()->removeItem($ticket);
						$player->sendMessage(ToolCore::Prefix."강화에 실패하였습니다");
					}
				}
			}
			$player->removeCurrentWindow();
		}
		return new InvMenuTransactionResult(true);
	}

	public function onClose(Player $player): void
	{
		$player->getInventory()->addItem($this->getInventory()->getItem(self::SLOT_INPUT_ITEM));
		$player->getInventory()->addItem($this->getInventory()->getItem(self::SLOT_INPUT_TICKET));
		parent::onClose($player);
	}
}