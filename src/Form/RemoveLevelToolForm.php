<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\LevelToolUtil;
use pocketmine\item\Tool;
use pocketmine\player\Player;

final class RemoveLevelToolForm extends SelectLevelToolForm
{
    public function jsonSerialize(): array
    {
        return $this->makeJson("레벨도구 제거", "제거할 레벨도구를 선택해주세요");
    }

    protected function handleSelect(Player $player, Tool $tool): void
    {
        LevelToolUtil::$levelTools = array_filter(LevelToolUtil::$levelTools, fn(Tool $value) => $value !== $tool);
        $player->sendMessage(ToolCore::Prefix . "{$tool->getName()}(이)가 제거되었습니다");
    }
}