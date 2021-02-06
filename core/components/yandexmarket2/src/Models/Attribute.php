<?php

namespace YandexMarket\Models;

use ymFieldAttribute;

/**
 * @property int $id
 * @property string $name
 * @property int $field_id
 * @property null|string $value
 * @property null|string $handler
 * @property null|array $properties
 */
class Attribute extends BaseObject
{
    public const TYPE_STRING  = 0;
    public const TYPE_DATE    = 1;
    public const TYPE_BOOLEAN = 2;
    public const TYPE_SELECT  = 3;

    public const TYPE_DEFAULT = self::TYPE_STRING;

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

    public function getType(): int
    {
        return $this->properties['type'] ?? self::TYPE_DEFAULT;
    }
}