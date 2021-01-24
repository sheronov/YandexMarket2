<?php

namespace YandexMarket\Models;

use ymFieldAttribute;

/**
 * @property int $id
 * @property string $name
 * @property int $field_id
 * @property null|string $column
 * @property null|string $handler
 */
class Attribute extends BaseObject
{

    public static function getObjectClass(): string
    {
        return ymFieldAttribute::class;
    }

    public function getField(): Field
    {
        return new Field($this->xpdo, $this->object->getOne('Field'));
    }
}