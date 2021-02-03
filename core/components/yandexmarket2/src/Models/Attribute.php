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
    //where it using? TODO: add support of attribute types
    public const TYPE_VALUE   = 0;
    public const TYPE_DATE    = 1;
    public const TYPE_BOOLEAN = 2;
    public const TYPE_TEXT    = 3;
    public const TYPE_SELECT  = 4;

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
            $this->field = new Field($this->modx, $this->object->getOne('Field'));
        }
        return $this->field;
    }
}