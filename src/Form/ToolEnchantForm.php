<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\ToolCore;
use DOHWI\ToolCore\Util\EnchantUtil;
use pocketmine\form\Form;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class ToolEnchantForm implements Form
{
	private int $efficiency;
	private int $fortune;
	private int $silkTouch;
	private int $unbreaking;
	private bool $infinity;

	private const MAX_ENCHANTMENT_LEVEL = 10;

	public function __construct(private readonly Item $tool)
	{
		$this->efficiency = $this->tool->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY());
		$this->fortune = $this->tool->getEnchantmentLevel(EnchantUtil::$fortune);
		$this->silkTouch = $this->tool->getEnchantmentLevel(VanillaEnchantments::SILK_TOUCH());
		$this->unbreaking = $this->tool->getEnchantmentLevel(VanillaEnchantments::UNBREAKING());
		$this->infinity = $this->tool->isUnbreakable();
	}

	public function jsonSerialize(): array
	{
		return [
			"type" => "custom_form",
			"title" => "§l도구 인챈트",
			"content" => [
				[
					"type" => "input",
					"text" => "- 효율성 (최대수치 : ".self::MAX_ENCHANTMENT_LEVEL.")",
					"default" => (string) $this->efficiency
				],
				[
					"type" => "input",
					"text" => "- 희귀품 채굴 (최대수치 : ".self::MAX_ENCHANTMENT_LEVEL.")",
					"default" => (string) $this->fortune
				],
				[
					"type" => "input",
					"text" => "- 섬세한 손길 (최대수치 : ".self::MAX_ENCHANTMENT_LEVEL.")",
					"default" => (string) $this->silkTouch
				],
				[
					"type" => "input",
					"text" => "- 견고 (최대수치 : ".self::MAX_ENCHANTMENT_LEVEL.")",
					"default" => (string) $this->unbreaking
				],
				[
					"type" => "toggle",
					"text" => "- 내구도 무한",
					"default" => $this->infinity
				]
			]
		];
	}

	public function handleResponse(Player $player, $data): void
	{
		if($data === null) return;
		$efficiency = (int) $data[0];
		$fortune = (int) $data[1];
		$silkTouch = (int) $data[2];
		$unbreaking = (int) $data[3];
		$infinity = (bool) $data[4];
		if($efficiency > self::MAX_ENCHANTMENT_LEVEL || $fortune > self::MAX_ENCHANTMENT_LEVEL || $silkTouch > self::MAX_ENCHANTMENT_LEVEL || $unbreaking > self::MAX_ENCHANTMENT_LEVEL) {
			$player->sendMessage(ToolCore::Prefix."인챈트 수치가 허용된 범위를 벗어났습니다.");
			return;
		}
		EnchantUtil::updateEnchantment($this->tool, VanillaEnchantments::EFFICIENCY(), $efficiency, self::MAX_ENCHANTMENT_LEVEL);
		EnchantUtil::updateEnchantment($this->tool, EnchantUtil::$fortune, $fortune, self::MAX_ENCHANTMENT_LEVEL);
		EnchantUtil::updateEnchantment($this->tool, VanillaEnchantments::SILK_TOUCH(), $silkTouch, self::MAX_ENCHANTMENT_LEVEL);
		EnchantUtil::updateEnchantment($this->tool, VanillaEnchantments::UNBREAKING(), $unbreaking, self::MAX_ENCHANTMENT_LEVEL);
		$this->tool->setUnbreakable($infinity);
		$player->getInventory()->setItemInHand($this->tool);
		$player->sendMessage(ToolCore::Prefix."인챈트를 적용했습니다");
	}
}