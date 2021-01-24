<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Exception;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Marketplaces\YandexMarket;
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

    public static function getObjectClass(): string
    {
        return ymPricelist::class;
    }

    public function getFields(bool $withAttributes = true): array
    {
        if (!isset($this->fields)) {
            $this->fields = [];
            foreach ($this->object->getMany('Fields') as $ymField) {
                $field = new Field($this->xpdo, $ymField);
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

            $q = $this->xpdo->newQuery(ymFieldAttribute::class);
            $q->where(['field_id:IN' => $fieldIds]);

            $this->fieldsAttributes = array_map(function (ymFieldAttribute $attribute) {
                return new Attribute($this->xpdo, $attribute);
            }, $this->xpdo->getCollection(ymFieldAttribute::class, $q) ?? []);
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
                $category = new Category($this->xpdo, $ymCategory);
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

        foreach ($this->getFields() as $field) {
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

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if ($shopField = $this->getFieldByName('shop')) {
            $data['tree'] = $this->makeFieldsTree($shopField->id);
        }

        // TODO: может сделать группы для полей [shop, categories, offer] (чтобы на фронте легче разбивать по группам)
        $data['fields'] = $this->getFieldsData(); // fields[ 'shop' => getFieldsData(), 'categories' => (), 'offer' => getOfferFields()]

        $data['values'] = [
            'shop'       => $this->getShopData(),
            'categories' => array_values(array_map(static function (Category $categoryObject) {
                return $categoryObject->resource_id;
            }, $this->getCategories())),
            'offer'      => $this->getOfferData()
        ];

        $data['offer_fields'] = $this->getOfferFields();

        $data['shop'] = $this->getShopData();
        $data['categories'] = array_values(array_map(static function (Category $categoryObject) {
            return $categoryObject->resource_id;
        }, $this->getCategories()));
        $data['offer'] = $this->getOfferData();

        return $data;
    }

    public function getShopData(): array
    {
        return [
            'name'                  => $this->xpdo->getOption('site_name', 'Test'),
            'company'               => 'Рога и копыта',
            'url'                   => $this->xpdo->getOption('site_url'),
            'currencies'            => ['RUB'],
            'enable_auto_discounts' => false,
            'platform'              => 'MODX Revolution',
            'version'               => $this->xpdo->getOption('settings_version')
        ];
    }

    public function getOfferData(): array
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

    public function getOfferFields(): array
    {
        return YandexMarket::getOfferFields();
    }

    protected function getFieldsData(): array
    {
        if ($marketplace = Marketplace::getMarketPlace($this->object->get('type'))) {
            return $marketplace::getShopFields();
        }
        return [];
    }
}