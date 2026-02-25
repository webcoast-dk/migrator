<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Utility;

class ArrayUtility
{
    public static function removeEmptyValuesFromArray(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::removeEmptyValuesFromArray($value);
            }

            if ($value === null || (is_array($value) && empty($value))) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
