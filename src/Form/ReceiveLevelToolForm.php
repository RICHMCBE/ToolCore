<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use pocketmine\item\Tool;
use pocketmine\player\Player;

final class ReceiveLevelToolForm extends SelectLevelToolForm
{
    public function jsonSerialize(): array
    {
        return $this->makeJson("레벨도구 지급", "지급받을 레벨도구를 선택해주세요");
    }

    protected function handleSelect(Player $player, Tool $tool): void
    {
        $inventory = $player->getInventory();
        if (!$inventory->canAddItem($tool)) {
            $player->sendMessage(ToolCore::Prefix . "인벤토리를 비운 후 다시 시도해주세요");
            return;
        }
        $player->getInventory()->addItem($tool);
        $player->sendMessage(ToolCore::Prefix . "{$tool->getName()}(이)가 지급되었습니다");
    }
}