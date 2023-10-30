<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use DOHWI\ToolCore\Util\ToolCoreLoreUtil;
use pocketmine\form\Form;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\player\Player;
use function mt_rand;

final class ToolBeyondForm implements Form
{
    private readonly int $beforeLevel;
    private readonly float $chance;

    public function __construct(private readonly Pickaxe|Axe|Hoe $tool)
    {
        $this->beforeLevel = ToolBeyondUtil::getBeyondLevel($this->tool);
        $this->chance = ToolBeyondUtil::CHANCES[$this->beforeLevel];
    }

    public function jsonSerialize(): array
    {
        return [
            "type" => "modal",
            "title" => "§l도구 초월",
            "content" => implode("\n§r", [
                "현재 초월 레벨 : {$this->beforeLevel}",
                "초월 성공확률 : {$this->chance}/100",
                "",
                "정말로 초월을 시도하시겠습니까 ?"
            ]),
            "button1" => "초월하기",
            "button2" => "취소하기"
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if (!$data) return;
        $rand = rand(1, 1000) * 0.1;
        $player->getInventory()->removeItem(ToolBeyondUtil::$beyondStone);
        if ($rand <= $this->chance) {
            $player->getInventory()->removeItem($this->tool);
            if ($this->beforeLevel === 0) {
                $this->tool->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
                $this->tool->setUnbreakable();
            }
            ToolCoreLoreUtil::update($this->tool);
            $player->getInventory()->addItem(ToolBeyondUtil::setBeyondLevel($this->tool, $this->beforeLevel + 1));
            $player->sendMessage(ToolCore::Prefix . "축하합니다 ! 초월에 성공하였습니다 !");
        } else {
            $player->sendMessage(ToolCore::Prefix . "초월에 실패하였습니다");
	        if ($this->beforeLevel === 0) return;
			$rand = mt_rand(1,100);
			if ($rand <= 10 * ($this->beforeLevel + 1)){
				$player->sendMessage(ToolCore::Prefix . "도구가 힘을 버티지 못하고 터져버렸습니다 !");
				$player->getInventory()->removeItem($this->tool);
			}
        }
    }
}