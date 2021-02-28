<?php

namespace YandexMarket\Xml;

use Exception;
use Jevix;
use modResource;
use modX;
use pdoTools;
use XMLWriter;
use YandexMarket\Models\Attribute;
use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;

abstract class PricelistWriter
{
    /** @var XMLWriter */
    protected $xml;
    /** @var Jevix */
    protected $jevix;
    /** @var Pricelist */
    protected $pricelist;
    /** @var null|pdoTools */
    protected $pdoTools;
    /** @var modX */
    protected $modx;
    protected $errors  = [];
    protected $start   = 0;
    protected $preview = false;
    protected $log     = [];

    public function __construct(Pricelist $pricelist, modX $modx)
    {
        $this->pricelist = $pricelist;
        $this->modx = $modx;
        $this->initializeJevix();
        if (!$this->initializePdoTools()) {
            $this->errorLog('Could not load pdoTools. Code handlers will be skipped');
        }
        $this->xml = new XMLWriter();
        $this->start = microtime(true);
    }

    /**
     * @param  bool  $asString
     * @param  string  $separator
     *
     * @return string|array
     */
    public function getLog(bool $asString = false, string $separator = PHP_EOL)
    {
        return $asString ? implode($separator, $this->log) : $this->log;
    }

    protected function writeHeader(): void
    {
        $this->xml->startDocument('1.0', 'UTF-8');
        $this->xml->setIndent(true);
        $this->xml->setIndentString("\t");
    }

    protected function writeComment(string $comment, bool $indentAfter = true): void
    {
        $this->xml->writeComment($comment);
        if ($indentAfter) {
            $this->xml->setIndent(true);
        }
    }

    protected function initializeJevix(): void
    {
        try {
            $this->jevix = new Jevix();
            $this->jevix->cfgAllowTags(['h3', 'ul', 'ol', 'li', 'p', 'br']);
            $this->jevix->cfgSetTagChilds('ul', 'li', true, false);
            $this->jevix->cfgSetTagChilds('ol', 'li', true, false);
            $this->jevix->cfgSetTagNoAutoBr(['ul', 'ol']);
            $this->jevix->cfgSetAutoBrMode(false);
        } catch (Exception $exception) {
            $this->errorLog($exception->getMessage());
            $this->errors[] = $exception->getMessage();
        }
    }

    /**
     * @param  Field  $field
     * @param  array  $pls
     * @param  array  $skipTypes
     *
     * @throws Exception
     */
    protected function writeField(Field $field, array $pls = [], array $skipTypes = []): void
    {
        if (!empty($skipTypes) && in_array($field->type, $skipTypes, true)) {
            if ($this->preview) {
                $this->writeComment(" элемент {$field->name} пропущен ");
            }
            return;
        }
        switch ($field->type) {
            case Field::TYPE_TEXT:
            case Field::TYPE_VALUE:
            case Field::TYPE_CDATA_VALUE:
            case Field::TYPE_CATEGORY:
                $this->writeValuableField($field, $pls);
                break;
            case Field::TYPE_CATEGORIES:
                $this->writeCategoriesField($field, $pls);
                break;
            case Field::TYPE_OFFERS:
                $this->writeOffersField($field, $pls);
                break;
            case Field::TYPE_CURRENCIES:
                $this->writeCurrenciesField($field, $pls);
                break;
            case Field::TYPE_PICTURES:
                // TODO: implement here !!!
                $this->writeComment(' Изображения товара ');
                break;
            case Field::TYPE_ROOT:
            case Field::TYPE_SHOP:
            case Field::TYPE_PARENT:
            case Field::TYPE_OFFER:
                if (!$children = $field->getChildren()) {
                    return;
                }
                $this->xml->startElement($field->name);//
                $this->writeAttributes($field->getAttributes(), $pls);
                foreach ($children as $child) {
                    $this->writeField($child, $pls, $skipTypes);
                }
                $this->xml->endElement();
                break;
            case Field::TYPE_EMPTY:
                if ($attributes = $field->getAttributes()) {
                    $this->xml->startElement($field->name);
                    $this->writeAttributes($attributes, $pls);
                    $this->xml->endElement();
                }
                break;
            default:
                $this->errorLog("Undefined type {$field->type} for field {$field->name} ({$field->id})");
        }
    }

    /**
     * Поле, которое может иметь значение
     *
     * @param  Field  $field
     * @param  array  $pls
     *
     * @throws Exception
     */
    protected function writeValuableField(Field $field, array $pls = []): void
    {
        $value = null;

        switch ($field->type) {
            case Field::TYPE_TEXT:
                $value = $field->value;
                break;
            case Field::TYPE_VALUE:
            case Field::TYPE_CATEGORY:
            case Field::TYPE_CDATA_VALUE:
                $value = $this->prepareValue($this->resolveColumn($field->value, $pls), $field->handler, $pls);
                break;
        }
        if ($value === '' || $value === null) {
            //пустые значения пропускаются, даже если у них есть атрибуты
            if ($this->preview && $field->properties['required'] ?? false) {
                $this->writeComment("Пустое значение для обязательного элемента {$field->name}");
            }
            return;
        }

        $this->xml->startElement($field->name);

        $this->writeAttributes($field->getAttributes(), $pls);

        if ($field->type === Field::TYPE_CDATA_VALUE) {
            $this->xml->writeCdata($this->jevix ? $this->jevix->parse($value, $this->errors) : $value);
        } else {
            $this->xml->text($value);
        }

        $this->xml->endElement();
    }

    /**
     * @param  Attribute[]  $attributes
     * @param  array  $pls
     */
    protected function writeAttributes(array $attributes, array $pls = []): void
    {
        foreach ($attributes as $attribute) {
            $this->writeFieldAttribute($attribute, $pls);
        }
    }

    protected function writeFieldAttribute(Attribute $attribute, array $pls = []): void
    {
        $value = null;
        switch ($attribute->getType()) {
            case Attribute::TYPE_TEXT:
                $value = $attribute->value;
                break;
            case Attribute::TYPE_VALUE:
                $value = $this->prepareValue($this->resolveColumn($attribute->value, $pls), $attribute->handler, $pls);
                break;
            default:
                $this->errorLog("Undefined type {$attribute->getType()} for attribute {$attribute->name} ({$attribute->id})");
        }

        if ($value !== null && $value !== '') {
            $this->xml->writeAttribute($attribute->name, $value);
        }
    }

    protected function resolveColumn(string $column, array $pls = [])
    {
        $value = null;
        if (isset($pls['offer']) || mb_strpos($column, '.') !== false) {
            $object = explode('.', $column)[0];
            $field = explode('.', $column, 2)[1] ?? null;
            switch (mb_strtolower($object)) {
                case 'setting':
                    $value = $this->modx->getOption($field); //можно даже в полях указывать Setting.some_setting
                    break;
                case 'pricelist':
                    $value = $this->pricelist->get($field);
                    break;
                case 'category':
                    if (($category = $pls['category'] ?? null) && $category instanceof Category) {
                        $value = $category->get($column);
                    } else {
                        $value = $pls[$column] ?? null;
                    }
                    break;
                case 'offer':
                default: //все остальные объекты проксируются в оффер, он уже сам разрулит
                    if (($offer = $pls['offer'] ?? null) && $offer instanceof Offer) {
                        $value = $offer->get($column);
                    } else {
                        $value = $pls[$column] ?? null;
                    }
                    break;
            }
        } elseif (($resource = $pls['resource'] ?? null) && $resource instanceof modResource) {
            $value = $resource->get($column);
        } elseif (isset($pls[$column])) {
            $value = $pls[$column];
        }

        return $value;
    }

    protected function prepareValue(?string $value, ?string $handler, array $pls = []): ?string
    {
        if (!empty($handler) && $this->pdoTools) {
            if (mb_stripos(trim($handler), '@INLINE') !== 0) {
                $handler = '@INLINE '.trim($handler);
            }
            //TODO: все объекты из pls - сделать в toArray() (у кого нет такого метода - удалить)
            $value = $this->pdoTools->getChunk($handler, array_merge($pls, [
                'input'     => $value,
                'pricelist' => $this->pricelist
            ]), true);
        }
        return $value;
    }

    protected function initializePdoTools(): bool
    {
        $pdoTools = $this->modx->getService('pdoTools');
        if ($pdoTools && $pdoTools instanceof pdoTools) {
            $this->pdoTools = $pdoTools;
            return true;
        }
        return false;
    }

    protected function writeCurrenciesField(Field $field, array $pls = []): void
    {
        $currencyElement = $field->getProperties()['child'] ?? 'currency';
        $rateAttr = $field->getProperties()['rate'] ?? 'rate';
        $rateValue = $field->getProperties()['rate_value'] ?? '1';

        if (!empty($field->value)) {
            $currencies = json_decode($field->value, true);

            if (!empty($currencies)) {
                $this->xml->startElement($field->name);
                $this->writeAttributes($field->getAttributes(), $pls);
                foreach ($currencies as $i => $currency) {
                    $this->xml->startElement($currencyElement);
                    $this->xml->writeAttribute('id', $currency);
                    if (!$i) {
                        $this->xml->writeAttribute($rateAttr, $rateValue);
                    }
                    $this->xml->endElement();
                }
                $this->xml->endElement();
            }
        }
    }

    protected function writeCategoriesField(Field $field, array $pls = []): void
    {
        $this->xml->startElement($field->name);
        $this->writeAttributes($field->getAttributes(), $pls);

        if (!$categories = $this->pricelist->getCategories()) {
            $categories = $this->pricelist->suitableOffersCategoriesGenerator();
        }

        if (($children = $field->getChildren()) && $categoryField = reset($children)) {
            // foreach ($categories as $category) {
            //     $this->writeCategoryTree($category, $field);
            // }
            foreach ($categories as $category) {
                $pls['category'] = $category;
                $pls['resource'] = $category->getResource();
                $this->writeField($categoryField, $pls);
            }
        } else {
            $this->errorLog("Empty children for field {$field->name} ({$field->id})");
        }

        $this->xml->endElement();
    }

    protected function writeCategoryTree(Category $category, Field $fieldCategories): void
    {
        // TODO: сделать как с оффером, добавить тип Field::TYPE_CATEGORY (где разрешить выбрать поле для названия и атрибут)
        /** @var modResource $resource */
        $resource = $category->getResource();
        $this->xml->startElement($fieldCategories->getProperties()['child'] ?? 'category');
        $this->xml->writeAttribute($fieldCategories->getProperties()['id_attribute'] ?? 'id', $resource->get('id'));
        if ($parentId = $resource->get('parent')) {
            $this->xml->writeAttribute($fieldCategories->getProperties()['parent_attribute'] ?? 'parentId', $parentId);
        }
        // TODO: нужно где-то предложить выбор, чтобы могли указать для родителя не pagetitle, а другое поле (или Fenom)
        $this->xml->text($resource->get($fieldCategories->getProperties()['resource_column'] ?? 'pagetitle'));

        $this->xml->endElement();
    }

    protected function writeOffersField(Field $field, array $pls = []): void
    {
        $this->xml->startElement($field->name);
        $this->writeAttributes($field->getAttributes(), $pls);

        $offers = $this->pricelist->offersGenerator();
        if (($children = $field->getChildren()) && $offerField = reset($children)) {
            foreach ($offers as $offer) {
                $pls['offer'] = $offer;
                $this->writeField($offerField, $pls);
            }
        } else {
            $this->errorLog("Empty children for field {$field->name} ({$field->id})");
        }

        $this->xml->endElement();
    }

    protected function log(string $message, bool $withTime = true): void
    {
        if ($withTime) {
            $message = sprintf("%2.4f s: %s", (microtime(true) - $this->start), $message);
        }
        $this->log[] = $message;
    }

    protected function errorLog(string $message): void
    {
        $this->log($message);
        $this->modx->log(modX::LOG_LEVEL_ERROR, '[YandexMarket2] '.$message);
    }

}