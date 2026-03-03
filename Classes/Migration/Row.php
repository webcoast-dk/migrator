<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

/**
 * @extends \iterable<Column>
 * @method Column current()
 */
class Row extends \SplObjectStorage
{
    public function attach(object $object, mixed $info = null): void
    {
        if (!$object instanceof Column) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', Column::class, get_debug_type($object)));
        }

        parent::attach($object, $info);
    }
}
