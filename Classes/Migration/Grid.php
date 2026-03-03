<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

/**
 * @extends \iterable<Row>
 * @method Row current()
 */
class Grid extends \SplObjectStorage
{
    public function attach(object $object, mixed $info = null): void
    {
        if (!$object instanceof Row) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', Row::class, get_debug_type($object)));
        }

        parent::attach($object, $info);
    }
}
