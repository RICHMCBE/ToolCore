<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Tool;

final class ToolCoreLoreUtil
{
    public static function update(Tool $tool): Tool
    {
        $lore = [];
        if (LevelToolUtil::isLevelTool($tool)) {
            $lore[] = "§r§l§b레벨도구 정보 :";
            $lore[] = "§r- 도구 주인 : " . LevelToolUtil::getOwner($tool);
            $lore[] = "§r- 도구 정보 : " . LevelToolUtil::getLevel($tool) . "레벨";
            $lore[] = "§r- 경험치 정보 : " . LevelToolUtil::getExp($tool) . "/" . LevelToolUtil::getMaxExp($tool) . " EXP";
            $lore[] = "";
        }
        if (ToolBeyondUtil::isBeyond($tool)) {
            $lore[] = "§r§l§b초월정보 :";
            $lore[] = "§r- 초월 레벨 : " . ToolBeyondUtil::getBeyondLevel($tool);
            $lore[] = "";
            $lore[] = "§r§l§b초월 옵션 :";
            if ($tool instanceof Pickaxe) {
                $lore[] = "§r- LV 1 : 내구도 무한";
                $lore[] = "§r- LV 2 : 태양열";
                $lore[] = "§r- LV 3 : 성급함";
            } else if ($tool instanceof Axe) {
                $lore[] = "§r- LV 1 : 내구도 무한";
                $lore[] = "§r- LV 2 : 자동줍기";
                $lore[] = "§r- LV 3 : 돈 추가습득";
            } else if ($tool instanceof Hoe) {
                $lore[] = "§r- LV 1 : 내구도 무한";
                $lore[] = "§r- LV 2 : 넓은 범위 경작";
            }
        }
        $tool->setLore($lore);
        return $tool;
    }
}