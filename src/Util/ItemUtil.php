<?php

declare(strict_types=1);

namespace DOHWI\ToolCore\Util;

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

final class ItemUtil
{
    public static function Serialize(Item $item): string
    {
        return json_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize())));
    }

    public static function Deserialize(string $serializedItem): ?Item
    {
        $item = Item::nbtDeserialize((new LittleEndianNbtSerializer())->read(json_decode($serializedItem, true))->mustGetCompoundTag());
        return $item->isNull() ? null : $item;
    }
}