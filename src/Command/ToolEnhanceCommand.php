<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Command;

use DOHWI\ToolCore\Inventory\LevelToolEnhanceInventory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

final class ToolEnhanceCommand extends Command
{
    public function __construct()
    {
        parent::__construct("도구강화", "§r§7도구를 강화하는 명령어입니다");
        $this->setPermission(DefaultPermissions::ROOT_USER);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player || !$this->testPermission($sender)) return;
        (new LevelToolEnhanceInventory())->send($sender);
    }
}