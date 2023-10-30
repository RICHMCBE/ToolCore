<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Listener;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\EnchantUtil;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use DOHWI\ToolCore\Util\ToolCoreLoreUtil;
use pocketmine\block\Crops;
use pocketmine\block\Dirt;
use pocketmine\block\Grass;
use pocketmine\block\Melon;
use pocketmine\block\Pumpkin;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\utils\Limits;
use RoMo\MoneyCore\wallet\WalletFactory;

final class EventListener implements Listener{
    private static array $expBlockStateIds = [];
    private const HOE_GROUNDING_EXP = 1; // 괭이로 땅 갈면 주는 경험치 양

    public function __construct(){
        self::$expBlockStateIds = [
            [ // 곡괭이
              VanillaBlocks::COAL_ORE()->getTypeId() => 3,
              VanillaBlocks::IRON_ORE()->getTypeId() => 8,
              VanillaBlocks::GOLD_ORE()->getTypeId() => 10,
              VanillaBlocks::DIAMOND_ORE()->getTypeId() => 20,
              VanillaBlocks::EMERALD_ORE()->getTypeId() => 25,
              VanillaBlocks::LAPIS_LAZULI_ORE()->getTypeId() => 5,
              VanillaBlocks::REDSTONE_ORE()->getTypeId() => 5,
            ],
            [ // 도끼
              VanillaBlocks::ACACIA_WOOD()->getTypeId() => 5,
              VanillaBlocks::BIRCH_WOOD()->getTypeId() => 5,
              VanillaBlocks::OAK_WOOD()->getTypeId() => 5,
              VanillaBlocks::DARK_OAK_WOOD()->getTypeId() => 5,
              VanillaBlocks::JUNGLE_WOOD()->getTypeId() => 5,
              VanillaBlocks::MANGROVE_WOOD()->getTypeId() => 5,
              VanillaBlocks::SPRUCE_WOOD()->getTypeId() => 5,
              VanillaBlocks::PUMPKIN()->getTypeId() => 15,
              VanillaBlocks::MELON()->getTypeId() => 5
            ],
            [ // 괭이
              VanillaBlocks::BEETROOTS()->getTypeId() => 20,
              VanillaBlocks::WHEAT()->getTypeId() => 20,
              VanillaBlocks::CARROTS()->getTypeId() => 15,
              VanillaBlocks::POTATOES()->getTypeId() => 15
            ]
        ];
    }

    /* 초월괭이 2레벨 효과 */
    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
        if(!$item instanceof Hoe) return;
        if(ToolBeyondUtil::getBeyondLevel($item) > 1){
            $centerBlock = $event->getBlock();
            $world = $player->getWorld();
            for($x = -1; $x < 2; $x++){
                for($z = -1; $z < 2; $z++){
                    $position = $centerBlock->getPosition()->add($x, 0, $z);
                    $block = $world->getBlock($position);
                    if($block instanceof Grass || $block instanceof Dirt) $world->setBlock($position, VanillaBlocks::FARMLAND());
                }
            }
        }
        if(LevelToolUtil::isLevelTool($item)){
            if(LevelToolUtil::isLevelUp($item, self::HOE_GROUNDING_EXP)){
                $level = LevelToolUtil::getLevel($item) + 1;
                $player->sendTitle("§l§e도구 레벨업", "도구의 레벨이 {$level}로 상승하였습니다 !", 10, 30, 10);
                if($level % 10 === 0){
                    $enchantLevel = (int) ($level / 10);
                    $item = EnchantUtil::updateEnchantment($item, VanillaEnchantments::UNBREAKING(), $enchantLevel, VanillaEnchantments::UNBREAKING()->getMaxLevel());
                    $player->sendTitle("§c§l어라..?", "레벨도구에 변화가 생긴 것 같다 !", 10, 30, 10);
                    $player->getInventory()->setItemInHand($item);
                }
            }
            $player->getInventory()->setItemInHand(LevelToolUtil::addExp($item, self::HOE_GROUNDING_EXP));
        }
    }

    public function onPlace(BlockPlaceEvent $event) : void{
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        if(Server::getInstance()->isOp($player->getName())) return;
        if($block instanceof Pumpkin || $block instanceof Melon) $event->cancel();
    }

    public function onHeld(PlayerItemHeldEvent $event) : void{
        $player = $event->getPlayer();
        $playerId = $player->getId();
        $item = $event->getItem();
        if($item instanceof Tool){
            $item->setUnbreakable();
            $item->setDamage(0);
        }
        if($item instanceof Pickaxe && ToolBeyondUtil::getBeyondLevel($item) > 2){
            self::$playerQueue[$playerId] = $playerId;
            $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), Limits::INT32_MAX, 2, false));
        }else if(isset(self::$playerQueue[$playerId])){
            $player->getEffects()->remove(VanillaEffects::HASTE());
        }
    }

    /* 초월곡괭이 레벨 3레벨 효과 */
    private static array $playerQueue = [];

    public function onBreak(BlockBreakEvent $event) : void{
        $player = $event->getPlayer();
        $tool = $event->getItem();
        $block = $event->getBlock();
        if(!$tool instanceof Tool) return;
        /* 초월도구 */
        if(ToolBeyondUtil::isBeyond($tool)){
            $level = ToolBeyondUtil::getBeyondLevel($tool);
            if ($tool instanceof Pickaxe && $level > 1) {
                $dropCount = count($block->getDrops($tool));
                if ($block->getTypeId() === VanillaBlocks::IRON_ORE()->getTypeId()) {
                    $event->setDrops([VanillaItems::IRON_INGOT()->setCount($dropCount)]);
                } else if ($block->getTypeId() === VanillaBlocks::GOLD_ORE()->getTypeId()) {
                    $event->setDrops([VanillaItems::GOLD_INGOT()->setCount($dropCount)]);
                }
            }else if($tool instanceof Axe && $level > 1){
                $player->getInventory()->addItem(...$block->getDrops($tool));
                $event->setDrops([]);
                if($level > 2 && rand(1, 100) <= 5){
                    $amount = rand(100, 1000);
                    WalletFactory::getInstance()->getWallet($player->getName())->addCoin($amount);
                    $player->sendMessage(ToolCore::Prefix . "초월효과로 {$amount}코인을 지급받았습니다");
                }
            }
        }
        /* 레벨도구 */
        if(LevelToolUtil::isLevelTool($tool)){
            if(LevelToolUtil::getOwner($tool) === LevelToolUtil::KEY_NOT_HAS_OWNER){
                $player->sendTitle("§b레벨도구", "레벨도구를 사용하여 도구에 이름이 새겨졌습니다");
                LevelToolUtil::setOwner($tool, $player->getName());
                $player->getInventory()->setItemInHand(ToolCoreLoreUtil::update($tool));
            }
            $toolId = match (true) {
                $tool instanceof Pickaxe => 0,
                $tool instanceof Axe => 1,
                $tool instanceof Hoe => 2,
            };
            if(isset(self::$expBlockStateIds[$toolId][$block->getTypeId()])){
                if($block instanceof Crops && $block->getAge() < Crops::MAX_AGE) return;
                $expAmount = self::$expBlockStateIds[$toolId][$block->getTypeId()];
                if(LevelToolUtil::isLevelUp($tool, $expAmount)){
                    $level = LevelToolUtil::getLevel($tool) + 1;
                    $player->sendTitle("§l§e도구 레벨업", "도구의 레벨이 {$level}로 상승하였습니다!", 10, 30, 10);
                    if($level % 10 === 0){
                        $enchantLevel = (int) ($level / 10);
                        $tool = EnchantUtil::updateEnchantment($tool, VanillaEnchantments::UNBREAKING(), $enchantLevel, VanillaEnchantments::UNBREAKING()->getMaxLevel());
                        if($toolId !== 2){
                            $tool = EnchantUtil::updateEnchantment($tool, VanillaEnchantments::EFFICIENCY(), $enchantLevel, VanillaEnchantments::EFFICIENCY()->getMaxLevel());
                            $tool = EnchantUtil::updateEnchantment($tool, EnchantUtil::$fortune, $enchantLevel, EnchantUtil::$fortune->getMaxLevel());
                        }
                        $player->sendTitle("§c§l어라..?", "레벨도구에 변화가 생긴 것 같다!", 10, 30, 10);
                        $player->getInventory()->setItemInHand($tool);
                    }
                }
                $player->getInventory()->setItemInHand(LevelToolUtil::addExp($tool, $expAmount));
            }
        }
    }
}