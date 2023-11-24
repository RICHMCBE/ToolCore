<?php

declare(strict_types=1);

namespace DOHWI\ToolCore;

use DOHWI\ToolCore\Command\ToolBeyondCommand;
use DOHWI\ToolCore\Command\ToolBeyondManageCommand;
use DOHWI\ToolCore\Command\ToolCoreCommand;
use DOHWI\ToolCore\Command\ToolEnhanceCommand;
use DOHWI\ToolCore\Listener\EventListener;
use DOHWI\ToolCore\Util\EnchantUtil;
use DOHWI\ToolCore\Util\ItemUtil;
use DOHWI\ToolCore\Util\LevelToolUtil;
use DOHWI\ToolCore\Util\ToolBeyondUtil;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\Tool;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;

final class ToolCore extends PluginBase
{
    public const Prefix = "§r§6ㆍ§r§f";

    private static Config $config;

    private const KEY_BEYOND_STONE = "BEYOND_STONE";
    private const KEY_LEVEL_TOOLS = "LEVEL_TOOLS";

    protected function onEnable(): void
    {
        EnchantUtil::registerFortune($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new ToolCoreCommand(),
            new ToolEnhanceCommand(),
            new ToolBeyondCommand(),
	        new ToolBeyondManageCommand()
        ]);

        self::$config = new Config(Path::join($this->getDataFolder(), "data.yml"), Config::YAML);

        $beyondStone = self::$config->get(self::KEY_BEYOND_STONE);
        ToolBeyondUtil::$beyondStone = $beyondStone ? ItemUtil::Deserialize($beyondStone) : false;
        LevelToolUtil::$levelTools = array_map(fn(string $tool) => ItemUtil::Deserialize($tool), self::$config->get(self::KEY_LEVEL_TOOLS, []));

        /**InvMenuHandler::register($this);**/
    }

    protected function onDisable(): void
    {
        self::$config->setAll([
            self::KEY_BEYOND_STONE => ToolBeyondUtil::$beyondStone ? ItemUtil::Serialize(ToolBeyondUtil::$beyondStone) : false,
            self::KEY_LEVEL_TOOLS => array_map(fn(Tool $tool) => ItemUtil::Serialize($tool), LevelToolUtil::$levelTools)
        ]);
        self::$config->save();
    }
}