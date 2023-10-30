<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Command;

use DOHWI\ToolCore\Form\ToolBeyondForm;
use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use DOHWI\ToolCore\Util\ToolCoreLoreUtil;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Tool;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

final class ToolBeyondManageCommand extends Command
{
	public function __construct()
	{
		parent::__construct("초강", "§r§7도구를 초월하는 명령어입니다");
		$this->setPermission(DefaultPermissions::ROOT_OPERATOR);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if(!$sender instanceof Player || !$this->testPermission($sender)) return;
		$hand = $sender->getInventory()->getItemInHand();
		if(!isset($args[0])) {
			$sender->sendMessage(ToolCore::Prefix."사용법: /초강 [레벨]");
			return;
		}
		if(!is_numeric($args[0])) {
			$sender->sendMessage(ToolCore::Prefix."숫자 값을 입력해주세요");
			return;
		}
		$TLevel = (int) $args[0];

		if(!$hand instanceof Tool) {
			$sender->sendMessage(ToolCore::Prefix."레벨도구에만 초월을 할 수 있습니다");
			return;
		}
		if(!LevelToolUtil::isLevelTool($hand)) {
			$sender->sendMessage(ToolCore::Prefix."레벨도구에만 초월을 할 수 있습니다");
			return;
		}
		$toolId = match (true) {
			$hand instanceof Pickaxe => 0,
			$hand instanceof Axe => 1,
			$hand instanceof Hoe => 2,
			default => false
		};
		if($toolId !== false) {
			if($TLevel > 3) {
				$sender->sendMessage(ToolCore::Prefix."3레벨까지만 초월이 가능합니다");
				return;
			}
			if(($hand instanceof Hoe && $TLevel > 2)) {
				$sender->sendMessage(ToolCore::Prefix."괭이는 2레벨까지만 초월이 가능합니다");
				return;
			} else if($TLevel <= 0) {
				$sender->sendMessage(ToolCore::Prefix."1레벨부터 초월이 가능합니다");
				return;
			}
			$tool = $hand;
			$sender->getInventory()->removeItem($hand);
			$tool->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
			$tool->setUnbreakable();
			$sender->getInventory()->addItem(ToolCoreLoreUtil::update(ToolBeyondUtil::setBeyondLevel($tool, $TLevel)));
		} else {
			$sender->sendMessage(ToolCore::Prefix."해당 아이템은 초월시킬 수 없어요");
		}
	}
}