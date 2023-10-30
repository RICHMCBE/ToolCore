<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;

final class ToolEnhanceUtil
{
    private const KEY_TICKET = "TICKET";
    private const VALUE_TICKET = "LEVEL_TOOL_ENHANCE_TICKET";

    public static function isTicket(Item $item): bool
    {
        return (bool)$item->getNamedTag()->getTag(self::KEY_TICKET);
    }

    public static function makeTicket(Item $item, Enchantment $enchantment): Item
    {
        $item->getNamedTag()->setString(self::KEY_TICKET, self::VALUE_TICKET);
        $item->removeEnchantments();
        $item->addEnchantment(new EnchantmentInstance($enchantment, 100));
        return $item;
    }

    public static function getEnchantment(Item $item): Enchantment
    {
        $enchants = $item->getEnchantments();
        $enchant = array_shift($enchants);
        return $enchant->getType();
    }
}