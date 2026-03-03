<?php

declare(strict_types=1);

namespace WEBcoast\Migrator\Migration;

/**
 * @extends \iterable<Field>
 * @method Field current()
 */
class FieldCollection extends \SplObjectStorage
{
    public function addField(Field $field): void
    {
        $this->attach($field);
    }

    public function attach(object $object, mixed $info = null): void
    {
        if (!$object instanceof Field) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', Field::class, get_debug_type($object)));
        }

        parent::attach($object, $info);
    }
}
