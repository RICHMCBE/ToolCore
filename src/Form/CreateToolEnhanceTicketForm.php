<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\EnchantUtil;
use DOHWI\ToolCore\Util\ToolEnhanceUtil;
use pocketmine\form\Form;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;

final class CreateToolEnhanceTicketForm implements Form
{
    private const KEY_TICKET = "TICKET";
    private const VALUE_TICKET = "LEVEL_TOOL_ENHANCE_TICKET";

    public function jsonSerialize(): array
    {
        return [
            "type" => "form",
            "title" => "§l도구강화 티켓 생성",
            "content" => "- 손에 들고있는 아이템이 티켓으로 생성됩니다\n- 설정할 도구강화의 타입을 선택해주세요",
            "buttons" => [
                ["text" => "효율"],
                ["text" => "견고"],
                ["text" => "희귀품 채굴"]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null) return;
        $item = $player->getInventory()->getItemInHand();
        if ($item->isNull()) {
            $player->sendMessage(ToolCore::Prefix . "해당 아이템은 도구강화권으로 등록할 수 없습니다");
            return;
        }
        $item->getNamedTag()->setString(self::KEY_TICKET, self::VALUE_TICKET);
        $player->getInventory()->setItemInHand(ToolEnhanceUtil::makeTicket($item, match ($data) {
            0 => VanillaEnchantments::EFFICIENCY(),
            1 => VanillaEnchantments::UNBREAKING(),
            2 => EnchantUtil::$fortune
        }));
        $player->sendMessage(ToolCore::Prefix . "도구강화권이 생성되었습니다");
    }
}