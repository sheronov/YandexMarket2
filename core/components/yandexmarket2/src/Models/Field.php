<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use ymField;
use ymFieldAttribute;

/**
 * @property int $id
 * @property string $name
 * @property null|int $parent
 * @property int $type
 * @property int $pricelist_id
 * @property null|string $column
 * @property null|string $handler
 * @property null|array $properties
 * @property int $rank
 * @property DateTimeImmutable $created_on
 * @property bool $active
 */
class Field extends BaseObject
{
    //любое значение может быть записано в column и дополнительно обработано в handler
    public const TYPE_ROOT       = 0;
    public const TYPE_PARENT     = 1; //обёртка без своего собственного значения
    public const TYPE_SHOP       = 2; // поле магазин (сюда будут прокинуты SHOP_FIELDS)
    public const TYPE_CURRENCIES = 4; // валюта
    public const TYPE_CATEGORIES = 5; // категории
    public const TYPE_OFFERS     = 6; // предложения
    public const TYPE_OFFER      = 7; // предложение
    public const TYPE_OPTION     = 8; //чисто текстовое значение (не будет как-либо заменяться)
    public const TYPE_FEATURE    = 9; // ещё не реализовано

    public const TYPE_STRING   = 10; //просто боле текстовое (подходит всегда)
    public const TYPE_CDATA    = 11; //большой текст, обернуть в CDATA
    public const TYPE_NUMBER   = 12; //числовое предложения
    public const TYPE_BOOLEAN  = 13; //выбор да/нет (игнорировать null - intermediate)
    public const TYPE_PARAM    = 14; // параметр предложения TODO: может и не нужно, иначе будет неудобно обрабатывать по типу
    public const TYPE_PICTURES = 15; // изображения предложения

    public const TYPE_DEFAULT = self::TYPE_STRING; //поле по умолчанию

    /** @var null|Field */
    protected $parentField;
    /** @var null|Field[] */
    protected $children;
    /** @var null|Attribute[] */
    protected $attributes;
    /** @var Pricelist */
    protected $pricelist;

    public static function getObjectClass(): string
    {
        return ymField::class;
    }

    public function getPricelist(): Pricelist
    {
        if (!isset($this->pricelist)) {
            $this->pricelist = new Pricelist($this->xpdo, $this->object->getOne('Pricelist'));
        }
        return $this->pricelist;
    }

    public function getAttributes(): array
    {
        if (!isset($this->attributes)) {
            $this->attributes = array_map(function (ymFieldAttribute $attribute) {
                return new Attribute($this->xpdo, $attribute);
            }, $this->object->getMany('Attributes'));
        }
        return $this->attributes;
    }

    public function addAttribute(Attribute $attribute): Field
    {
        if (!isset($this->attributes)) {
            $this->attributes = [];
        }
        $this->attributes[] = $attribute->setField($this);

        return $this;
    }

    public function setParent(Field $parent): Field
    {
        $this->parentField = $parent;
        return $this;
    }

    public function getParent(): ?Field
    {
        return $this->parentField;
    }

    public function addChildren(Field $child): void
    {
        if (!isset($this->children)) {
            $this->children = [];
        }
        $this->children[] = $child->setParent($this);
    }

    public function getChildren(): ?array
    {
        return $this->children;
    }

}