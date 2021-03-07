<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Generator;
use modX;
use PDO;
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
 * @property string $type
 * @property string $class
 * @property bool $active
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

    protected $groupedBy = [];

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
            $data['where'] = json_encode($data['where'], JSON_UNESCAPED_UNICODE);
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

    /**
     * Генератор предложений для дальнейшей итерации
     *
     * @param  array  $config
     *
     * @return Generator
     */
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

    /**
     * Удобный метод для получения количества предложений
     *
     * @return int
     */
    public function offersCount(): int
    {
        $query = $this->queryForOffers();
        return $this->modx->getCount($query->getClass(), $query);
    }

    /**
     * Подготовка запроса для получения товаров
     *
     * @return xPDOQuery
     */
    protected function queryForOffers(): xPDOQuery
    {
        $q = $this->modx->newQuery($this->class);

        $offerColumns = $this->modx->getSelectColumns($q->getClass(), $q->getClass(), '');
        $q->select($offerColumns);
        // TODO: на будущее для интеграций пример SQL для получения постоянного ID предложения при группировках
        //CONCAT(`msProduct`.`id`, 'x', SUBSTR(md5(`option.color`.`value`), 1, 19 - LENGTH(`msProduct`.`id`))) as id

        $this->addColumnsToGroupBy($offerColumns);

        if (!empty($this->getCategories())) {
            $q->where([
                'parent:IN' => array_map(static function (Category $category) {
                    return $category->resource_id;
                }, $this->getCategories())
            ]);
        }

        if (Service::hasMiniShop2()) {
            if (mb_strtolower($this->class) === mb_strtolower('msProduct')) {
                $q->join('msProductData', 'Data');
            } else {
                $q->leftJoin('msProductData', 'Data');
            }
            $q->leftJoin('msVendor', 'Vendor', 'Data.vendor=Vendor.id');
            $dataColumns = $this->modx->getSelectColumns('msProductData', 'Data', '', ['id'], true);
            $vendorColumns = $this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor.', ['id'], true);
            $q->select($dataColumns);
            $q->select($vendorColumns);

            $this->addColumnsToGroupBy($dataColumns);
            $this->addColumnsToGroupBy($vendorColumns);
        }

        $this->joinPricelistFields($q, $this->getFields(true));

        foreach ($this->groupedBy as $column) {
            $q->groupby($column);
        }

        return $q;
    }

    protected function addColumnsToGroupBy(string $columns): void
    {
        foreach (explode(', ', $columns) as $column) {
            if (mb_strpos($column, ' AS ') !== false) {
                $column = explode(' AS ', $column)[0];
            }
            $this->groupedBy[] = $column;
        }
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
            if ($field->type === Field::TYPE_PICTURE) {
                $count = $field->properties['count'] ?? 10;
                if ($count > 10) {
                    $count = 10;
                }

                switch (mb_strtolower($field->value)) {
                    case 'msgallery.image':
                    case 'msproductfile.url':
                        $alias = "`msgallery`";
                        $q->leftJoin('msProductFile', $alias,
                            "{$alias}.`product_id` = `{$q->getClass()}`.`id` and {$alias}.`parent` = 0 and {$alias}.`type` = 'image'");
                        break;
                    case 'ms2gallery.image':
                    case 'msresourcefile.url':
                        $alias = "`ms2gallery`";
                        $q->leftJoin('msResourceFile', $alias,
                            "{$alias}.`resource_id` = `{$q->getClass()}`.`id` and {$alias}.`parent` = 0  and {$alias}.`type` = 'image'");
                        break;
                    default:
                        $this->modx->log(modX::LOG_LEVEL_ERROR,
                            '[YandexMarket2] Can not process picture field "'.$field->value.'". Check the documentation.');
                        continue 2;
                }
                $q->select(["SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT {$alias}.`url` ORDER BY `rank` ASC SEPARATOR '||'), '||', {$count}) as {$alias}"]);

                continue;
            }

            if (!empty($field->value) && mb_strpos($field->value, '.') !== false) {
                [$class, $key] = explode('.', $field->value, 2);
                if (!in_array($key, $classKeys[mb_strtolower($class)] ?? [], true)) {
                    $classKeys[mb_strtolower($class)][] = $key;
                }
            }
            if (!empty($field->handler) && preg_match_all('/{\$([A-z]+)\.([0-9A-z-_]+)[^}]*}/m', $field->handler,
                    $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    [, $class, $key] = $match;
                    if (!in_array($key, $classKeys[mb_strtolower($class)] ?? [], true)) {
                        $classKeys[mb_strtolower($class)][] = $key;
                    }
                }
            }
        }

        // TODO: тут сделать через стороннюю таблицу условий
        if (!empty($this->where)) {
            $q->where(json_decode($this->where, true, 512, JSON_UNESCAPED_UNICODE));
            if (preg_match_all('/"([A-z]+)\.([0-9A-z-_]+)[^"]*"/m', $this->where, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    [, $class, $key] = $match;
                    if (!in_array($key, $classKeys[mb_strtolower($class)] ?? [], true)) {
                        $classKeys[mb_strtolower($class)][] = $key;
                    }
                }
            }
        }

        foreach ($classKeys as $class => $keys) {
            switch (mb_strtolower($class)) {
                case 'offer':
                case 'resource':
                case 'modresource':
                case 'product':
                case 'data':
                case 'msproduct':
                case 'msproductdata':
                case 'vendor':
                case 'msVendor':
                    //стандартные классы, не нужно ничего джойнить здесь
                    break;
                case 'tv':
                    $qTvs = $this->modx->newQuery('modTemplateVar');
                    $qTvs->where(['name:IN' => $keys]);
                    foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                        /** @var \modTemplateVar $tv */
                        $alias = "`tv.{$tv->name}`";
                        $q->leftJoin('modTemplateVarResource', $alias,
                            "{$alias}.`contentid` = `{$q->getClass()}`.`id` and {$alias}.`tmplvarid` = {$tv->id}");
                        $q->select("{$alias}.`value` as {$alias}");
                        $this->groupedBy[] = "{$alias}.`value`";
                    }
                    break;
                case 'option';
                    $qOptions = $this->modx->newQuery('msOption');
                    $qOptions->where(['key:IN' => $keys]);
                    foreach ($this->modx->getIterator($qOptions->getClass(), $qOptions) as $option) {
                        /** @var \msOption $option */
                        $alias = "`option.{$option->get('key')}`";
                        $q->leftJoin('msProductOption', $alias,
                            "{$alias}.`product_id` = `{$q->getClass()}`.`id` and {$alias}.`key` = '{$option->get('key')}'");
                        if (!in_array("{$alias}.`value`", $this->groupedBy, true)
                            && in_array($option->get('type'), ['combo-multiple', 'combo-options'], true)) {
                            $q->select("GROUP_CONCAT(DISTINCT {$alias}.`value` SEPARATOR '||') as {$alias}");
                        } else {
                            $q->select("{$alias}.`value` as {$alias}");
                            $this->groupedBy[] = "{$alias}.`value`";
                        }
                    }
                    break;
                default:
                    // TODO: приджойнить выбранные столбцы других классов (с моделями что-то придумать нужно)
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        '[YandexMarket2] Unimplemented class '.$class.'. Please contact to the support.');
                    break;
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

    /**
     * Если же категории не выбраны - нужно вернуть все те, что в товарах участвуют (а не все сайта)
     * Снова генератор для уменьшения памяти
     *
     * @return Generator
     */
    public function suitableOffersCategoriesGenerator(): Generator
    {
        $parentIds = [];
        $q = $this->queryForOffers();
        $q->query['columns'] = '';
        $q->select('DISTINCT `'.$q->getClass().'`.`parent`');

        if ($q->prepare() && $q->stmt->execute()) {
            $parentIds = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (!empty($parentIds)) {
            $q = $this->modx->newQuery('modResource');
            $q->sortby('parent');
            $q->sortby('id');
            $q->where(['id:IN' => $parentIds]);
            foreach ($this->modx->getIterator('modResource', $q) as $resource) {
                $category = new Category($this->modx);
                $category->setResource($resource);
                yield $category;
            }
        }
    }
}