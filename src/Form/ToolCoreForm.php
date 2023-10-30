<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\EnchantUtil;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use pocketmine\form\Form;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Tool;
use pocketmine\player\Player;

final class ToolCoreForm implements Form
{
    public function jsonSerialize(): array
    {
        return [
            "type" => "form",
            "title" => "§l도구코어",
            "content" => "- 하실 작업을 선택해주세요",
            "buttons" => [
                ["text" => "§l초월석 등록\n§r§8손에 들고있는 아이템을 초월석으로 등록합니다"],
                ["text" => "§l인챈트\n§r§8손에 들고있는 아이템을 인챈트합니다"],
                ["text" => "§l레벨도구 등록\n§r§8손에 들고있는 도구를 레벨도구로 등록합니다"],
                ["text" => "§l레벨도구 받기\n§r§8등록되어있는 레벨도구를 받습니다"],
                ["text" => "§l레벨도구 제거\n§r§8등록되어있는 레벨도구를 제거합니다"],
                ["text" => "§l도구강화권 생성\n§r§8도구강화권을 생성합니다"]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null) return;
        $hand = $player->getInventory()->getItemInHand();
        if ($data === 0) {
            if ($hand->isNull()) {
                $player->sendMessage(ToolCore::Prefix . "해당 아이템은 초월석으로 등록할 수 없습니다");
                return;
            }
            ToolBeyondUtil::$beyondStone = $hand->setCount(1);
            $player->sendMessage(ToolCore::Prefix . "초월석이 등록되었어요");
        } else if ($data === 1) {
            $player->sendForm(new ToolEnchantForm($hand));
        } else if ($data === 2) {
            if (!$hand instanceof Tool) {
                $player->sendMessage(ToolCore::Prefix . "해당 아이템은 레벨도구로 등록할 수 없습니다");
                return;
            }
            if (LevelToolUtil::isLevelTool($hand)) {
                $player->sendMessage(ToolCore::Prefix . "해당 도구는 이미 레벨도구로 등록되어있습니다");
                return;
            }
            LevelToolUtil::addLevelTool($hand);
            if ($hand instanceof Pickaxe) EnchantUtil::updateEnchantment($hand, VanillaEnchantments::EFFICIENCY(), 1, 1);
            elseif ($hand instanceof Hoe) EnchantUtil::updateEnchantment($hand, VanillaEnchantments::UNBREAKING(), 1, 1);
            $player->sendMessage(ToolCore::Prefix . "{$hand->getName()}(을)를 레벨도구로 등록했습니다");
            $player->getInventory()->setItemInHand($hand);
        } else if ($data === 3) {
            $player->sendForm(new ReceiveLevelToolForm());
        } else if ($data === 4) {
            $player->sendForm(new RemoveLevelToolForm());
        } else if ($data === 5) {
            $player->sendForm(new CreateToolEnhanceTicketForm());
        }
    }
}