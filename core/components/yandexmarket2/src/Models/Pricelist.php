<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Generator;
use modX;
use xPDOObject;
use xPDOQuery;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Service;
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

    public function get(string $field)
    {
        $field = str_replace(['Pricelist.', 'pricelist.'], '', $field);
        return parent::get($field);
    }

    public function getWhere(): array
    {
        return empty($this->where) ? [] : json_decode($this->where, true);
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

            //It's reduces sql queries
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

    public function getFieldByType(int $type): ?Field
    {
        foreach ($this->getFields(true) as $field) {
            if ($field->type === $type) {
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

    public function getFilePath(bool $withFile = false): string
    {
        $path = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_path', null,
            '{assets_path}yandexmarket/'));
        if ($withFile) {
            $path .= $this->file;
        }

        return $path;
    }

    public function getFileUrl(bool $withFile = false): string
    {
        $url = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_url', null,
            '{site_url}/{assets_url}/yandexmarket/'));
        if ($withFile) {
            $url .= $this->file;
        }

        return preg_replace('/(?<!:)\/+/', '/', $url);
    }

    public function toArray(bool $withValues = false): array
    {
        $data = parent::toArray();
        if (!empty($data['where']) && is_array($data['where'])) {
            $data['where'] = json_encode($data['where']);
        }

        $data['path'] = $this->getFilePath();
        $data['fileUrl'] = $this->getFileUrl(true);

        if ($withValues) {
            $data['fields'] = array_map(static function (Field $field) {
                return $field->toArray();
            }, array_values($this->getFields(false)));

            $data['attributes'] = array_map(static function (Attribute $attribute) {
                return $attribute->toArray();
            }, array_values($this->getFieldsAttributes(array_keys($this->getFields(false)))));

            $data['categories'] = array_map(static function (Category $categoryObject) {
                return $categoryObject->resource_id;
            }, array_values($this->getCategories()));
        }

        return $data;
    }

    public function offersGenerator(array $config = []): Generator
    {
        $query = $this->queryForOffers();

        if ($sortBy = $config['sortBy'] ?? null) {
            $query->sortby($sortBy, $config['sortDir'] ?? 'ASC');
        }

        if ($limit = $config['limit'] ?? null) {
            $query->limit($limit, $config['offset'] ?? 0);
        }

        $offers = $this->modx->getIterator($query->getClass(), $query);
        foreach ($offers as $offer) {
            yield new Offer($this->modx, $offer);
        }
    }

    // TODO: тут получить товары со всеми возможными опциями и тв полями
    public function queryForOffers(array $data = [])
    {
        $hasMs2 = Service::hasMiniShop2();
        $offerClass = $this->modx->getOption('ym_option_offer_class', null, $hasMs2 ? 'msProduct' : 'modDocument');
        $class = $offerClass ?: 'modResource'; //если настройку пустой сделать
        $q = $this->modx->newQuery($class);
        $q->select($this->modx->getSelectColumns($class, $class, ''));

        if (!empty($this->getCategories())) {
            $q->where([
                'parent:IN' => array_map(static function (Category $category) {
                    return $category->resource_id;
                }, $this->getCategories())
            ]);
        }

        if (!empty($offerClass)) {
            $q->where(['class_key' => $offerClass]);
        }

        if ($hasMs2) {
            $q->leftJoin('msProductData', 'Data');
            $q->leftJoin('msVendor', 'Vendor', 'Data.vendor=Vendor.id');
            $q->select($this->modx->getSelectColumns('msProductData', 'Data', '', ['id'], true));
            $q->select($this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor.', ['id'], true));
        }

        if (!empty($this->getWhere())) {
            $q->where($this->getWhere());
        }

        // TODO: тут изучить все значения (и их хэнделры в выбранных полях)
        $this->joinPricelistFields($q, $this->getFields(true));

        return $q;
    }

    /**
     * @param  xPDOQuery  $q
     * @param  Field[]  $fields
     *
     * @return xPDOQuery
     */
    protected function joinPricelistFields(xPDOQuery $q, array $fields): xPDOQuery
    {
        $classKeys = [];

        foreach ($fields as $field) {
            if (in_array($field->type, [Field::TYPE_TEXT, Field::TYPE_CURRENCIES, Field::TYPE_CATEGORIES], true)) {
                continue;
            }
            if (!empty($field->value) && mb_strpos($field->value, '.') !== false) {
                [$class, $key] = explode('.', $field->value, 2);
                if (!isset($classKeys[$class])) {
                    $classKeys[$class] = [];
                }
                if (!in_array($key, $classKeys[$class], true)) {
                    $classKeys[$class][] = $key;
                }
            }
            // TODO if(!empty($field->handler)) с регулярками найти {$Option.color} или {$TV.size} или [[+tv.size]]
        }

        $this->modx->log(1, 'class keys '.var_export($classKeys, true));

        // TODO: приджойнить другие классы (с моделями что-то придумать нужно)
        foreach ($classKeys as $class => $keys) {
            switch ($class) {
            }
        }

        return $q;
    }

    public function newField(string $name, int $type = Field::TYPE_DEFAULT, bool $active = true): Field
    {
        $field = new Field($this->modx);
        $field->name = $name;
        $field->type = $type;
        $field->pricelist_id = $this->id;
        $field->created_on = new DateTimeImmutable();
        $field->active = $active;

        return $field;
    }

    public function offersCount(): int
    {
        $query = $this->queryForOffers();
        return $this->modx->getCount($query->getClass(), $query);
    }
}