<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Form;

use DOHWI\ToolCore\Util\LevelToolUtil;
use pocketmine\form\Form;
use pocketmine\item\Tool;
use pocketmine\player\Player;

abstract class SelectLevelToolForm implements Form
{
    protected array $buttons = [];
    protected array $tools = [];

    public function __construct()
    {
        foreach (LevelToolUtil::$levelTools as $tool) {
            $this->buttons[] = ["text" => $tool->getName()];
            $this->tools[] = $tool;
        }
    }

    final public function makeJson(string $title, string $content): array
    {
        return [
            "type" => "form",
            "title" => "§l$title",
            "content" => "- $content",
            "buttons" => $this->buttons
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null) return;
        $this->handleSelect($player, $this->tools[$data]);
    }

    abstract protected function handleSelect(Player $player, Tool $tool): void;
}