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
    protected $field;

    public static function getObjectClass(): string
    {
        return ymFieldAttribute::class;
    }

    public function setField(Field $field): Attribute
    {
        $this->field = $field;
        return $this;
    }

    public function getField(): Field
    {
        if (!isset($this->field)) {
            $this->field = new Field($this->xpdo, $this->object->getOne('Field'));
        }
        return $this->field;
    }
}