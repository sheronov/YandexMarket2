<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Iterator;
use modX;
use xPDOObject;
use YandexMarket\Marketplaces\Marketplace;
use ymFieldAttribute;
use ymPricelist;

/**
 * @property int $id
 * @property string $name
 * @property string $file
 * @property bool $active
 * @property int $type
 * @property DateTimeImmutable $created_on
 * @property null|DateTimeImmutable $edited_on
 * @property null|DateTimeImmutable $generated_on
 * @property null|int $generate_mode
 * @property null|int $generate_interval
 * @property bool $need_generate
 * @property null|string $where
 * @property null|array $properties //make here array by default
 */
class Pricelist extends BaseObject
{
    /** @var Category[] */
    protected $categories;
    /** @var Field[] */
    protected $fields;
    /** @var Attribute[] */
    protected $fieldsAttributes;

    /** @var Marketplace */
    protected $marketplace;

    public function __construct(modX $modx, xPDOObject $object = null)
    {
        parent::__construct($modx, $object);
        $this->marketplace = Marketplace::getMarketPlace($this->type, $modx);
    }

    public function getMarketplace(): Marketplace
    {
        return $this->marketplace;
    }

    public static function getObjectClass(): string
    {
        return ymPricelist::class;
    }

    public function getFields(bool $withAttributes = true): array
    {
        if (!isset($this->fields)) {
            $this->fields = [];
            foreach ($this->object->getMany('Fields') as $ymField) {
                $field = new Field($this->modx, $ymField);
                $this->fields[$field->id] = $field;
            }

            if ($withAttributes && $attributes = $this->getFieldsAttributes(array_keys($this->fields))) {
                foreach ($attributes as $attribute) {
                    if ($field = $this->fields[$attribute->field_id] ?? null) {
                        $field->addAttribute($attribute);
                    }
                }
            }

            uasort($this->fields, static function (Field $a, Field $b) {
                if ($a->rank === $b->rank) {
                    return 0;
                }
                return ($a->rank < $b->rank) ? -1 : 1;
            });

            foreach ($this->fields as $field) {
                if ($field->parent && $parent = $this->fields[$field->parent] ?? null) {
                    $parent->addChildren($field);
                }
            }
        }

        return $this->fields;
    }

    protected function getFieldsAttributes(array $fieldIds): array
    {
        if (!isset($this->fieldsAttributes)) {
            $this->fieldsAttributes = [];

            $q = $this->modx->newQuery(ymFieldAttribute::class);
            $q->where(['field_id:IN' => $fieldIds]);

            $this->fieldsAttributes = array_map(function (ymFieldAttribute $attribute) {
                return new Attribute($this->modx, $attribute);
            }, $this->modx->getCollection(ymFieldAttribute::class, $q) ?? []);
        }

        return $this->fieldsAttributes;
    }

    public function getRootNode(): ?Field
    {
        foreach ($this->getFields(true) as $field) {
            if (!$field->parent) {
                return $field;
            }
        }

        return null;
    }

    public function getFieldByName(string $name): ?Field
    {
        foreach ($this->getFields(true) as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }
        return null;
    }

    public function getCategories(): array
    {
        if (!isset($this->categories)) {
            $this->categories = [];
            foreach ($this->object->getMany('Categories') as $ymCategory) {
                $category = new Category($this->modx, $ymCategory);
                $this->categories[$category->id] = $category;
            }
        }

        return $this->categories;
    }

    protected function prepareField(Field $field): array
    {
        return [
            'name'       => $field->name,
            'type'       => $field->type,
            'parent'     => $field->parent,
            'column'     => $field->column,
            'handler'    => $field->handler,
            'properties' => $field->properties,
            'rank'       => $field->rank,
            'active'     => $field->active
        ];
    }

    public function makeFieldsTree(?int $parentId): array
    {
        $branch = [];

        foreach ($this->getFields(true) as $field) {
            if ($field->parent === $parentId) {
                $preparedField = $this->prepareField($field);
                if ($children = $this->makeFieldsTree($field->id)) {
                    $preparedField['children'] = $children;
                }
                $branch[] = $preparedField;
            }
        }

        return $branch;
    }

    public function toArray(bool $withFields = false): array
    {
        $data = parent::toArray();

        if ($withFields) {
            // if ($shopField = $this->getFieldByName('shop')) {
            //     $data['tree'] = $this->makeFieldsTree($shopField->id);
            // }

            // TODO: потом перенести это на фронт
            $data['fields'] = [
                'shop'  => $this->marketplace::getShopFields(),
                'offer' => $this->marketplace::getOfferFields()
            ];

            $data['values'] = [
                'shop'       => $this->getShopValues(),
                'offer'      => $this->getOfferValues(),
                'categories' => array_values(array_map(static function (Category $categoryObject) {
                    return $categoryObject->resource_id;
                }, $this->getCategories())),
            ];
        }

        return $data;
    }

    public function getShopValues(): array
    {
        if (!$field = $this->getFieldByName('shop')) {
            return [];
        }

        return $this->writeFieldTreeToArray($field, []);
    }

    protected function writeFieldTreeToArray(Field $field, array $values = []): array
    {
        if (in_array($field->type, [Field::TYPE_CATEGORIES, Field::TYPE_OFFERS], true)) {
            return $values;
        }
        // TODO: подумать над тем, чтобы атрибуты на фронт в отдельном поле
        if ($attributes = $field->getAttributes()) {
            foreach ($attributes as $i => $attribute) {
                $values['attribute'.$attribute->id] = [
                    'entity'  => 'attribute',
                    'name'    => $attribute->name,
                    'field'   => 'field'.$attribute->field_id,
                    'column'  => $attribute->column,
                    'handler' => $attribute->handler,
                    'rank'    => $i
                ];
            }
        }

        if ($field->isEditable()) {
            $values['field'.$field->id] = [
                'entity'     => 'field',
                'type'       => $field->type,
                'name'       => $field->name,
                'column'     => $field->getValue(),
                'parent'     => $field->parent ? 'field'.$field->parent : null,
                'handler'    => $field->handler,
                'properties' => $field->getProperties(),
                'rank'       => $field->rank,
                'active'     => $field->active,
                'label'      => $field->getLabel('shop'),
                'help'       => $field->getHelp('shop'),
            ];
        }

        if ($children = $field->getChildren()) {
            foreach ($children as $child) {
                $values = $this->writeFieldTreeToArray($child, $values);
            }
        }

        return $values;
    }

    public function getOfferValues(): array
    {
        // TODO: тут нужно сделать иначе, по ID из БД например, чтобы избежать множественных значений внутри одного
        return [
            'offer' => [
                'attributes' => [
                    'id'   => 'modResource.id',
                    'type' => ''
                ]
            ],
            'name'  => 'modResource.pagetitle',
            'param' => [
                [
                    'attributes' => [
                        'name' => ['handler' => 'Цвет']
                    ],
                    'column'     => 'Option.color'
                ],
                [
                    'attributes' => [
                        'name' => ['handler' => 'Размер']
                    ],
                    'handler'    => '@INLINE {$Data.color[0]}'
                ],
            ]
        ];
    }

    public function getPricelistOffers(array $where = []): Iterator
    {
        //TODO: переделать полностью
        $q = $this->modx->newQuery('msProduct');
        $q->where(array_merge($where, ['class_key' => 'msProduct']));
        $q->sortby('RAND()');
        return $this->modx->getIterator('msProduct', $q);
    }
}