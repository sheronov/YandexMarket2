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
    public const TYPE_TEXT       = 0; //текстовое значение (не будет как-либо обрабатываться)
    public const TYPE_ROOT       = 1; //корневой элемент
    public const TYPE_SHOP       = 2; //поле магазин (сюда будут прокинуты SHOP_FIELDS)
    public const TYPE_CURRENCIES = 3; //валюта
    public const TYPE_CATEGORIES = 4; //категории
    public const TYPE_OFFERS     = 5; //предложения
    public const TYPE_OFFER      = 6; //предложение
    public const TYPE_CATEGORY   = 7; //предложение

    public const TYPE_PARENT      = 10; //обёртка без своего собственного значения
    public const TYPE_VALUE       = 11; //значение из поля товара (подходит всегда)
    public const TYPE_CDATA_VALUE = 12; //значение из поля товара обернуть в CDATA
    public const TYPE_PICTURES    = 13; //изображения предложения

    public const TYPE_EMPTY = 20; //пустой, только для атрибутов

    public const TYPE_DEFAULT = self::TYPE_VALUE; //поле по умолчанию
    public const TYPES_DATA   = [
        Field::TYPE_VALUE       => ['group' => ['offer']],
        Field::TYPE_CDATA_VALUE => ['group' => ['offer']],
        Field::TYPE_TEXT        => ['group' => ['offer', 'shop']],
        Field::TYPE_PARENT      => ['group' => ['offer', 'shop']],
        Field::TYPE_EMPTY       => ['group' => ['offer', 'shop']],
        Field::TYPE_PICTURES    => ['group' => ['offer'], 'unique' => true],
        Field::TYPE_CURRENCIES  => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_CATEGORIES  => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_OFFERS      => ['group' => ['shop'], 'unique' => true],
        Field::TYPE_ROOT        => ['hidden' => true, 'root' => true, 'parent' => true],
        Field::TYPE_OFFER       => ['hidden' => true, 'root' => true, 'parent' => true],
        Field::TYPE_CATEGORY    => ['group' => ['categories'], 'hidden' => true, 'root' => true],
        Field::TYPE_SHOP        => ['hidden' => true, 'root' => true, 'parent' => true],
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

    public function getParent(): ?Field
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

    public function addChildren(Field $child): void
    {
        if (!isset($this->children)) {
            $this->children = [];
        }
        $this->children[] = $child->setParent($this);
    }

    public function getChildren(): ?array
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

    public function toFrontend(bool $withItself = false, array $skipChildrenTypes = []): array
    {
        $data = [];
        if ($withItself) {
            $data = $this->toArray();
            $data['attributes'] = [];
            if ($attributes = $this->getAttributes()) {
                foreach ($attributes as $attribute) {
                    $data['attributes']['attr'.$attribute->id] = $attribute->toArray();
                }
            }
        }

        if ($withItself) {
            $data['fields'] = [];
            $fields = &$data['fields'];
        } else {
            $fields = &$data;
        }

        foreach ($this->getChildren() as $child) {
            if (in_array($child->type, $skipChildrenTypes, true)) {
                continue;
            }
            $fields['field'.$child->id] = $child->toFrontend(true, $skipChildrenTypes);
        }

        return $data;
    }

    public function lexiconKey(?string $parent = null): string
    {
        if ($parent) {
            return "ym_{$this->getPricelist()->type}_{$parent}_{$this->name}";
        }
        return "ym_{$this->getPricelist()->type}_{$this->name}";
    }

    public function getLabel(?string $parent = null): string
    {
        if (!$label = $this->getLexicon($this->lexiconKey($parent), $this->lexiconKey())) {
            $label = $this->name;
        }

        if ($this->getProperties()['required'] ?? false) {
            $label .= ' *';
        }

        return $label;
    }

    public function getHelp(?string $parent = null): ?string
    {
        return $this->getLexicon($this->lexiconKey($parent).'_help', $this->lexiconKey().'_help');
    }

    public function getLexicon(string $key, string $fallbackKey = null): ?string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        if ($fallbackKey && $fallbackKey !== $key && $fallbackKey !== $lexicon = $this->modx->lexicon($fallbackKey)) {
            return $lexicon;
        }

        return null;
    }

    /**
     * Узлы не могут содержать своего значения, только дочерние поля или атрибуты
     *
     * @return bool
     */
    public function isValueless(): bool
    {
        return in_array($this->type, [
            self::TYPE_ROOT,
            self::TYPE_SHOP,
            self::TYPE_OFFERS,
            self::TYPE_CURRENCIES,
            self::TYPE_CATEGORIES,
            self::TYPE_OFFER,
            self::TYPE_PICTURES,
            self::TYPE_PARENT,
            self::TYPE_EMPTY
        ]);
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