<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Utility;

class TcaUtility
{
    public static function migrateItemsFormat(array $items): array
    {
        foreach ($items as &$item) {
            if (is_array($item) && !isset($item['label'], $item['value'])) {
                $item['label'] = $item[0];
                $item['value'] = $item[1];
                unset($item[0], $item[1]);

                if ($item[2] ?? null) {
                    $item['icon'] = $item[2];
                    unset($item[2]);
                }

                if ($item[3] ?? null) {
                    $item['group'] = $item[3];
                    unset($item[3]);
                }

                if ($item[4] ?? null) {
                    $item['description'] = $item[4];
                    unset($item[4]);
                }
            }
        }

        return $items;
    }
}
