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
    public const TYPE_PARENT     = 0; //обёртка без своего собственного значения
    public const TYPE_OPTION     = 1; //чисто текстовое значение из column
    public const TYPE_CURRENCIES = 2; //тип валюты для яндекс маркета TODO: лучше убрать, КМК
    public const TYPE_CATEGORIES = 3; // дерево категорий

    protected $parentField;
    protected $children = [];

    protected $attributes;
    protected $pricelist;

    // TODO: подумать над обработчиками типов (каждый тип сам решает, как он будет писаться в XML
    public const TYPES_HANDLER = [
        self::TYPE_PARENT => [__CLASS__, 'handlerParent']
    ];

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
        $this->children[] = $child->setParent($this);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

}