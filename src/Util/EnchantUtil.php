<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\block\CoalOre;
use pocketmine\block\Crops;
use pocketmine\block\DiamondOre;
use pocketmine\block\EmeraldOre;
use pocketmine\block\GoldOre;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\Melon;
use pocketmine\block\NetherQuartzOre;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\Pumpkin;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\EventPriority;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

final class EnchantUtil
{
    public static Enchantment $fortune;

    private static array $fortunePercents = [
        1 => 3,
        2 => 5,
        3 => 10,
        4 => 15,
        5 => 20,
        6 => 30,
        7 => 35,
        8 => 40,
        9 => 45,
        10 => 50
    ];

    public static function updateEnchantment(Item $item, Enchantment $enchantment, int $level, int $maxLevel): Item
    {
        if ($level > $maxLevel) $level = $maxLevel;
        if ($enchantment === VanillaEnchantments::UNBREAKING() && $item instanceof Durable) $item->setUnbreakable();
        $level <= 0 ? $item->removeEnchantment($enchantment) : $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
        return $item;
    }

    public static function registerFortune(Plugin $plugin): void
    {
        self::$fortune = new Enchantment(new Translatable("enchantment.fortune", []), Rarity::MYTHIC, ItemFlags::DIG, ItemFlags::SHEARS, 6);
        EnchantmentIdMap::getInstance()->register(EnchantmentIds::FORTUNE, self::$fortune);

        Server::getInstance()->getPluginManager()->registerEvent(BlockBreakEvent::class, static function (BlockBreakEvent $event): void {
            $item = $event->getItem();
            $block = $event->getBlock();
            if (!$item instanceof TieredTool || !$item->hasEnchantment(self::$fortune)) return;
            $fortuneLevel = $item->getEnchantmentLevel(self::$fortune);
            $percent = $fortuneLevel > 6 ? 100 : self::$fortunePercents[$fortuneLevel];
            if (mt_rand(0, 100) > $percent) return;
            if ($block instanceof Crops) {
                $drops = [];
                foreach ($block->getDropsForCompatibleTool($item) as $drop) $drops[] = $drop->setCount($drop->getCount() * mt_rand(1, 6));
                $event->setDrops($drops);
            } elseif ($block instanceof Melon){
                $event->setDrops([VanillaBlocks::MELON()->asItem()->setCount(mt_rand(1, 5))]);
            } elseif ($block instanceof Pumpkin) {
                $event->setDrops([VanillaBlocks::PUMPKIN()->asItem()->setCount(mt_rand(2, 5))]);
            } elseif ($block instanceof NetherWartPlant){
                $event->setDrops([VanillaBlocks::NETHER_WART()->asItem()->setCount(mt_rand(3,5))]);
            } elseif ($block instanceof IronOre or $block instanceof GoldOre or $block instanceof DiamondOre or $block instanceof LapisOre or $block instanceof CoalOre or $block instanceof EmeraldOre or $block instanceof NetherQuartzOre) {
                $drops = [];
                foreach ($block->getDropsForCompatibleTool($item) as $drop) $drops[] = $drop->setCount($drop->getCount() * mt_rand(1, 2));
                $event->setDrops($drops);
            }
        }, EventPriority::HIGH, $plugin);
    }
}