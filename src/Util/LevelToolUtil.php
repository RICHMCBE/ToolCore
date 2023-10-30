<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\item\Tool;

final class LevelToolUtil
{
    public const BASE_MAX_EXP_SIZE = 100;

    private const KEY_LEVEL = "LEVEL";
    private const KEY_EXP = "EXP";
    private const KEY_MAX_EXP = "MAX_EXP";
    private const KEY_OWNER = "OWNER";
    public const KEY_NOT_HAS_OWNER = "없음";

    /** @var Tool[] */
    public static array $levelTools;

    public static function isLevelTool(Tool $tool): bool
    {
        return (bool)$tool->getNamedTag()->getTag(self::KEY_LEVEL);
    }

    public static function addLevelTool(Tool $tool): void
    {
        self::setLevel($tool, 1);
        self::setExp($tool, 0);
        self::setMaxExp($tool, self::BASE_MAX_EXP_SIZE);
        self::setOwner($tool, self::KEY_NOT_HAS_OWNER);
        ToolCoreLoreUtil::update($tool);
        self::$levelTools[] = $tool;
    }

    public static function isLevelUp(Tool $tool, int $expAmount): bool
    {
        return self::getExp($tool) + $expAmount >= self::getMaxExp($tool);
    }

    public static function addExp(Tool $tool, int $expAmount): Tool
    {
        $exp = self::getExp($tool);
        $level = self::getLevel($tool);
        $maxExp = self::getMaxExp($tool);
        if (self::isLevelUp($tool, $expAmount)) {
            $exp = ($exp + $expAmount) % $maxExp;
            $level++;
            $maxExp = ($level * self::BASE_MAX_EXP_SIZE);
            self::setExp($tool, $exp);
            self::setLevel($tool, $level);
            self::setMaxExp($tool, $maxExp);
        } else {
            $exp = $exp + $expAmount;
            self::setExp($tool, $exp);
        }
        return ToolCoreLoreUtil::update($tool);
    }

    public static function getLevel(Tool $tool): int
    {
        return $tool->getNamedTag()->getInt(self::KEY_LEVEL);
    }

    public static function setLevel(Tool $tool, int $level): void
    {
        $tool->getNamedTag()->setInt(self::KEY_LEVEL, $level);
    }

    public static function getExp(Tool $tool): int
    {
        return $tool->getNamedTag()->getInt(self::KEY_EXP);
    }

    public static function setExp(Tool $tool, int $exp): void
    {
        $tool->getNamedTag()->setInt(self::KEY_EXP, $exp);
    }

    public static function getMaxExp(Tool $tool): int
    {
        return $tool->getNamedTag()->getInt(self::KEY_MAX_EXP);
    }

    public static function setMaxExp(Tool $tool, int $maxExp): void
    {
        $tool->getNamedTag()->setInt(self::KEY_MAX_EXP, $maxExp);
    }

    public static function getOwner(Tool $tool): string
    {
        return $tool->getNamedTag()->getString(self::KEY_OWNER);
    }

    public static function setOwner(Tool $tool, string $ownerName): void
    {
        $tool->getNamedTag()->setString(self::KEY_OWNER, $ownerName);
    }
}