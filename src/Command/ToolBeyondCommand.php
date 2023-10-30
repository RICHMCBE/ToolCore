<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Command;

use DOHWI\ToolCore\Form\ToolBeyondForm;
use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Tool;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

final class ToolBeyondCommand extends Command
{
    private const BEYOND_LEVEL_LIMITS = [
        0 => 100, // 곡괭이 레벨제한
        1 => 100, // 도끼 레벨제한
        2 => 100 // 괭이 레벨제한
    ];

    public function __construct()
    {
        parent::__construct("초월강화", "§r§7도구를 초월하는 명령어입니다");
        $this->setPermission(DefaultPermissions::ROOT_USER);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player || !$this->testPermission($sender)) return;
        $hand = $sender->getInventory()->getItemInHand();
        if (!ToolBeyondUtil::$beyondStone) {
            $sender->sendMessage(ToolCore::Prefix . "아직 초월석이 등록되어있지 않아, 사용이 불가능합니다");
            return;
        }
        if (!$hand instanceof Tool) {
            $sender->sendMessage(ToolCore::Prefix . "레벨도구에만 초월을 할 수 있습니다");
            return;
        }
		if(!LevelToolUtil::isLevelTool($hand)) {
			$sender->sendMessage(ToolCore::Prefix . "레벨도구에만 초월을 할 수 있습니다");
			return;
		}
        if (!$sender->getInventory()->contains(ToolBeyondUtil::$beyondStone)) {
            $sender->sendMessage(ToolCore::Prefix . "초월석을 소유하고있지 않습니다");
            return;
        }
        $toolId = match (true) {
            $hand instanceof Pickaxe => 0,
            $hand instanceof Axe => 1,
            $hand instanceof Hoe => 2,
            default => false
        };
        $levelLimit = self::BEYOND_LEVEL_LIMITS[$toolId];
        if($levelLimit > LevelToolUtil::getLevel($hand)) {
            $sender->sendMessage(ToolCore::Prefix."해당 도구는 레벨 {$levelLimit}이상만 초월할 수 있습니다");
            return;
        }
        if ($toolId !== false) {
            $level = ToolBeyondUtil::getBeyondLevel($hand);
            if (($hand instanceof Hoe && $level === 2) || $level === 3) {
                $sender->sendMessage(ToolCore::Prefix . "해당 도구는 최대 초월레벨을 달성하였습니다");
                return;
            }
            $sender->sendForm(new ToolBeyondForm($hand));
        } else {
            $sender->sendMessage(ToolCore::Prefix . "해당 아이템은 초월시킬 수 없어요");
        }
    }
}