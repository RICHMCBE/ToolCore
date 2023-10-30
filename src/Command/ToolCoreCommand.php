<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Command;

use DOHWI\ToolCore\Form\ToolCoreForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

final class ToolCoreCommand extends Command
{
    public function __construct()
    {
        parent::__construct("도구코어", "§r§7도구코어 관련 명령어입니다 (관리자)");
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player || !$this->testPermission($sender)) return;
        $sender->sendForm(new ToolCoreForm());
    }
}