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
    public const TYPE_PARENT = 0; //обёртка без своего собственного значения
    public const TYPE_TEXT   = 1; //чисто текстовое значение (не будет как-либо заменяться)

    public const TYPE_BOOLEAN = 2; //выбор да/нет (игнорировать null - intermediate)
    public const TYPE_NUMBER  = 3; //числовое предложения
    public const TYPE_CDATA   = 4; //большой текст, обернуть в CDATA
    public const TYPE_DEFAULT = 5; //просто боле текстовое (подходит всегда)

    //специальные типы
    public const TYPE_CURRENCIES = 7; // валюта
    public const TYPE_CATEGORIES = 8; // категории
    public const TYPE_OFFER      = 10; // предложение

    public const TYPE_OFFER_PARAM    = 11; // параметр предложения
    public const TYPE_OFFER_PICTURES = 12; // изображения предложения

    public const TYPE_FEATURE = 20; // ещё не реализовано

    /** @var null|Field */
    protected $parentField;
    /** @var null|Field[] */
    protected $children;
    /** @var null|Attribute[]*/
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
        if(!isset($this->children)) {
            $this->children = [];
        }
        $this->children[] = $child->setParent($this);
    }

    public function getChildren(): ?array
    {
        return $this->children;
    }

}