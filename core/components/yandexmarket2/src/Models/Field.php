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
 * @property null|string $value
 * @property null|string $handler
 * @property null|array $properties
 * @property int $rank
 * @property DateTimeImmutable $created_on
 * @property bool $active
 */
class Field extends BaseObject
{
    //любое значение может быть записано в value и дополнительно обработано в handler
    const TYPE_TEXT       = 0; //текстовое значение (не будет как-либо обрабатываться)
    const TYPE_ROOT       = 1; //корневой элемент
    const TYPE_SHOP       = 2; //поле магазин (сюда будут прокинуты SHOP_FIELDS)
    const TYPE_CURRENCIES = 3; //валюта
    const TYPE_CATEGORIES = 4; //категории
    const TYPE_OFFERS     = 5; //предложения
    const TYPE_OFFER      = 6; //предложение
    const TYPE_CATEGORY   = 7; //предложение

    const TYPE_PARENT      = 10; //обёртка без своего собственного значения
    const TYPE_VALUE       = 11; //значение из поля товара (подходит всегда)
    const TYPE_CDATA_VALUE = 12; //значение из поля товара обернуть в CDATA
    const TYPE_PICTURE     = 13; //изображения предложения

    const TYPE_EMPTY = 20; //пустой, только для атрибутов

    const TYPE_DEFAULT = self::TYPE_VALUE; //поле по умолчанию
    const TYPES_DATA   = [
        Field::TYPE_VALUE       => ['group' => ['offer']],
        Field::TYPE_CDATA_VALUE => ['group' => ['offer']],
        Field::TYPE_TEXT        => ['group' => ['offer', 'shop']],
        Field::TYPE_PARENT      => ['group' => ['offer', 'shop'], 'parent' => true],
        Field::TYPE_EMPTY       => ['group' => ['offer', 'shop']],
        Field::TYPE_PICTURE     => ['group' => ['offer']],
        Field::TYPE_CURRENCIES  => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_CATEGORIES  => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_OFFERS      => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_ROOT        => ['hidden' => true, 'parent' => true, 'single' => true],
        Field::TYPE_OFFER       => ['hidden' => true, 'parent' => true, 'single' => true],
        Field::TYPE_CATEGORY    => ['group' => ['categories'], 'hidden' => true, 'single' => true],
        Field::TYPE_SHOP        => ['hidden' => true, 'parent' => true, 'single' => true],
    ];

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

    public function isArrayValue(): bool
    {
        return $this->type === self::TYPE_CURRENCIES;
    }

    /**
     * @return array|string|null
     */
    public function getValue()
    {
        return $this->isArrayValue() ? json_decode($this->value, true) : $this->value;
    }

    public function getProperties(): array
    {
        return $this->properties ?? [];
    }

    public function getPricelist(): Pricelist
    {
        if (!isset($this->pricelist)) {
            $this->pricelist = new Pricelist($this->modx, $this->object->getOne('Pricelist'));
        }
        return $this->pricelist;
    }

    public function getAttributes(): array
    {
        if (!isset($this->attributes)) {
            $this->attributes = array_map(function (ymFieldAttribute $attribute) {
                return new Attribute($this->modx, $attribute);
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

    /**
     * @return Field|null
     */
    public function getParent()
    {
        if (!isset($this->parentField)) {
            if ($parent = $this->object->getOne('Parent')) {
                $this->parentField = new self($this->modx, $parent);
            } else {
                $this->parentField = null;
            }
        }
        return $this->parentField;
    }

    /**
     * @param  Field  $child
     * @return void
     */
    public function addChildren(Field $child)
    {
        if (!isset($this->children)) {
            $this->children = [];
        }
        $this->children[] = $child->setParent($this);
    }

    public function getChildren(): array
    {
        if (!isset($this->children)) {
            $this->children = array_map(function (ymField $field) {
                return new Field($this->modx, $field);
            }, $this->object->getMany('Children'));
        }

        return $this->children;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['is_array_value'] = $this->isArrayValue(); //в значении хранится массив
        $data['label'] = $this->getLabel($this->getParent()->name ?? null);
        $data['help'] = $this->getHelp($this->getParent()->name ?? null);
        $data['properties'] = $this->getProperties();
        $data['value'] = $this->getValue();
        if ($values = $this->getProperties()['values'] ?? []) {
            $data['values'] = array_map(function ($value) {
                return [
                    'value' => $value,
                    'text'  => $this->getLexicon($this->lexiconKey().'_value_'.$value) ?? $value
                ];
            }, $values);
        }

        return $data;
    }

    public function lexiconKey(string $parent = null): string
    {
        if ($parent !== null) {
            return "ym2_{$this->getPricelist()->type}_{$parent}_{$this->name}";
        }
        return "ym2_{$this->getPricelist()->type}_{$this->name}";
    }

    public function getLabel(string $parent = null): string
    {
        if (!$label = $this->getLexicon($this->lexiconKey($parent), $this->lexiconKey())) {
            $label = $this->name;
        }

        if ($this->getProperties()['required'] ?? false) {
            $label .= ' *';
        }

        return $label;
    }

    public function getHelp(string $parent = null): string
    {
        return $this->getLexicon($this->lexiconKey($parent).'_help', $this->lexiconKey().'_help');
    }

    public function getLexicon(string $key, string $fallbackKey = null): string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        if ($fallbackKey && $fallbackKey !== $key && $fallbackKey !== $lexicon = $this->modx->lexicon($fallbackKey)) {
            return $lexicon;
        }

        return '';
    }

    public function newAttribute(string $attrName): Attribute
    {
        $attribute = new Attribute($this->modx);
        $attribute->field_id = $this->id;
        $attribute->name = $attrName;

        $this->addAttribute($attribute);

        return $attribute;
    }

}