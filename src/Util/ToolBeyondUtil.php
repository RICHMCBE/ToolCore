<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\item\Item;
use pocketmine\item\Tool;

final class ToolBeyondUtil
{
    public static Item|false $beyondStone;

    private const KEY_BEYOND = "BEYOND";

    public const CHANCES = [
        0 => 5, // 0 -> 1 성공확률
        1 => 0.5, // 1 -> 2 성공확률
        2 => 0.1 // 2 -> 3 성공확률
    ];

    public static function isBeyond(Tool $tool): bool
    {
        return (bool)$tool->getNamedTag()->getTag(self::KEY_BEYOND);
    }

    public static function setBeyondLevel(Tool $tool, int $level): Tool
    {
        $tool->getNamedTag()->setByte(self::KEY_BEYOND, $level);
        return $tool;
    }

    public static function getBeyondLevel(Tool $tool): int
    {
        return $tool->getNamedTag()->getByte(self::KEY_BEYOND, 0);
    }
}