<?php

namespace YandexMarket\Models;

use PDO;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Service;

/**
 * @property int $id
 * @property string $name
 * @property string $file
 * @property string $type
 * @property string $class
 * @property bool $active
 * @property string $created_on
 * @property null|string $edited_on
 * @property null|string $generated_on
 * @property null|int $generate_mode
 * @property null|int $generate_interval
 * @property bool $need_generate
 * @property null|array $properties
 * @property YmPricelist|\YmPricelist $object
 */
class Pricelist extends BaseObject
{
    const GENERATE_MODE_MANUALLY   = 0;
    const GENERATE_MODE_AFTER_SAVE = 1;
    const GENERATE_MODE_CRON_ONLY  = 2;

    /** @var Condition[] */
    protected $conditions;
    /** @var Field[] */
    protected $fields;
    /** @var Attribute[] */
    protected $fieldsAttributes;

    /** @var null|Marketplace */
    protected $marketplace;

    /**
     * @param  \MODX\Revolution\modX|\modX  $modx
     * @param  \xPDO\om\xPDOObject|\xPDOObject|null  $object
     */
    public function __construct($modx, $object = null)
    {
        parent::__construct($modx, $object);
        $this->marketplace = Marketplace::getMarketPlace($this->type, $modx);
    }

    /** @inheritDoc */
    public static function getObjectClass(): string
    {
        return Service::isMODX3() ? YmPricelist::class : \YmPricelist::class;
    }

    /**
     * Геттер для класса объектов в прайс-листе.
     * Так как class может в себе содержать модификаторы через двоеточие
     */
    public function getClass(): string
    {
        return explode(':', $this->class ?: '')[0];
    }

    /*
     * Возвращает список модификаций
     */
    public function getModifiers(): array
    {
        $classModifiers = explode(':', $this->class ?: '');
        array_shift($classModifiers);
        return array_map(static function ($modifier) {
            return mb_strtolower($modifier);
        }, $classModifiers);
    }

    /**
     * Объект маркетплейса, если есть
     *
     * @return null|Marketplace
     */
    public function getMarketplace()
    {
        return $this->marketplace;
    }

    public function get(string $field)
    {
        $field = str_replace(['Pricelist.', 'pricelist.'], '', $field);
        return parent::get($field);
    }

    /** Дёргаем обновление объекта */
    public function touch()
    {
        $this->object->touch();
    }

    /**
     * Массив полей, которые используются в прайс-листе
     *
     * @param  bool  $withAttributes
     *
     * @return array|Field[]
     */
    public function getFields(bool $withAttributes = false): array
    {
        if (!isset($this->fields)) {
            $this->fields = [];


            $q = $this->modx->newQuery($this->getClassByAlias('Fields'))
                ->where(['pricelist_id' => $this->id])
                ->sortby('`rank`');
            foreach ($this->modx->getIterator($q->getClass(), $q) as $ymField) {
                $field = new Field($this->modx, $ymField);
                $this->fields[$field->id] = $field;
            }

            if ($withAttributes && $attributes = $this->getFieldsAttributes()) {
                foreach ($attributes as $attribute) {
                    if ($field = $this->fields[$attribute->field_id] ?? null) {
                        $field->addAttribute($attribute);
                    }
                }
            }

            //It's reduces sql queries
            foreach ($this->fields as $field) {
                if ($field->parent && $parent = $this->fields[$field->parent] ?? null) {
                    $parent->addChildren($field);
                }
            }
        }

        return $this->fields;
    }

    /** ! Не вызывать раньше, чем getFields */
    protected function getFieldsAttributes(): array
    {
        if (!isset($this->fieldsAttributes)) {
            $fieldsIds = isset($this->fields) ? array_keys($this->fields) : [];
            $this->fieldsAttributes = [];

            if (!empty($fieldsIds)) {
                $attributesClass = reset($this->fields)->getClassByAlias('Attributes');

                $q = $this->modx->newQuery($attributesClass)->where(['field_id:IN' => $fieldsIds]);
                $this->fieldsAttributes = array_map(function ($attribute) {
                    return new Attribute($this->modx, $attribute);
                }, $this->modx->getCollection($q->getClass(), $q) ?? []);
            }
        }

        return $this->fieldsAttributes;
    }

    /**
     * Массив условий к товарам прайс-листа
     * (с таким же успехом их можно к категориям сделать, но там визуально выбираются в 90% случаев)
     *
     * @return array|Condition[]
     */
    public function getConditions(): array
    {
        if (!isset($this->conditions)) {
            $this->conditions = [];

            $q = $this->modx->newQuery($this->getClassByAlias('Conditions'))
                ->where(['pricelist_id' => $this->id])
                ->sortby('id');
            foreach ($this->modx->getIterator($q->getClass(), $q) as $ymCondition) {
                $condition = new Condition($this->modx, $ymCondition);
                $this->conditions[$condition->id] = $condition;
            }
        }
        return $this->conditions;
    }

    public function toArray(bool $withValues = false): array
    {
        $data = parent::toArray();
        if (!empty($data['where']) && is_array($data['where'])) {
            $data['where'] = json_encode($data['where'], JSON_UNESCAPED_UNICODE);
        }

        $data['fileUrl'] = $this->getFileUrl(true);

        $data['conditions'] = array_map(static function (Condition $condition) {
            return $condition->toArray();
        }, array_values($this->getConditions()));

        if ($withValues) {
            $source = $this->getMarketplace();
            $this->modx->lexicon->load($source::getLexiconNs());

            $data['fields'] = array_map(static function (Field $field) {
                return $field->toArray();
            }, array_values($this->getFields()));

            $data['attributes'] = array_map(static function (Attribute $attribute) {
                return $attribute->toArray();
            }, array_values($this->getFieldsAttributes()));

            $data['categories'] = $this->selectedCategoriesId();
        }

        return $data;
    }

    public function getFilePath(bool $withFile = false): string
    {
        $path = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_path', null,
            '{assets_path}yandexmarket/', true));
        if ($withFile) {
            $path .= $this->file;
        }

        return $path;
    }

    public function getFileUrl(bool $withFile = false): string
    {
        $url = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_url', null,
            '{site_url}/{assets_url}/yandexmarket/', true));
        if ($withFile) {
            $url .= $this->file;
        }

        return preg_replace('/(?<!:)\/+/', '/', $url);
    }

    public function selectedCategoriesId(): array
    {
        $ids = [];
        $q = $this->modx->newQuery($this->getClassByAlias('Categories'))
            ->where(['pricelist_id' => $this->id])
            ->select("DISTINCT `resource_id`");
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $ids = array_map(function ($categoryId) {
                return (int)$categoryId;
            }, $q->stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        return $ids;
    }

    /** Для более независимого добавления полей */
    public function newField(string $name, int $type = Field::TYPE_DEFAULT, bool $active = true): Field
    {
        $field = new Field($this->modx);
        $field->name = $name;
        $field->type = $type;
        $field->pricelist_id = $this->id;
        $field->created_on = date('Y-m-d H:i:s');
        $field->active = $active;

        return $field;
    }

    /** Для более независимого добавления условий к товарам */
    public function newCondition(string $column, string $operator, $value): Condition
    {
        $condition = new Condition($this->modx);
        $condition->column = $column;
        $condition->operator = $operator;
        $condition->value = $value;
        $condition->pricelist_id = $this->id;

        return $condition;
    }
}
