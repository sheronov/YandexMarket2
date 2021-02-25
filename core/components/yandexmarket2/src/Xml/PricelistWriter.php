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

class PricelistWriter
{
    /** @var XMLWriter $xml */
    protected $xml;
    /** @var Jevix $jevix */
    protected $jevix;
    /** @var Pricelist $pricelist */
    protected $pricelist;
    /** @var null|pdoTools $pdoTools */
    protected $pdoTools = null;
    protected $errors   = [];

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->initializeJevix();
        if (!$this->initializePdoTools()) {
            $this->errorLog('[YandexMarket] Could not load pdoTools. Code handlers will be skipped');
        }
        $this->xml = new XMLWriter();
    }

    protected function errorLog(string $message): void
    {
        $this->pricelist->modX()->log(modX::LOG_LEVEL_ERROR, $message);
    }

    public function writeHeader(): void
    {
        $this->xml->startDocument('1.0', 'UTF-8');
        $this->xml->setIndent(true);
        $this->xml->setIndentString("\t");
    }

    public function writeShopData(array $data): void
    {
        $this->xml->startElement('shop');
        foreach ($data as $key => $value) {
            $this->xml->startElement($value['name']);
            if ((int)$value['type'] === Field::TYPE_CURRENCIES) {
                foreach ($value['value'] as $i => $val) {
                    $this->xml->startElement('currency');
                    $this->xml->writeAttribute('id', $val);
                    if (!$i) {
                        $this->xml->writeAttribute('rate', 1);
                    }
                    $this->xml->endElement();
                }
            } elseif (($value['properties']['required'] ?? false) && ($value['value'] === null || $value['value'] === '')) {
                $this->writeComment('Это обязательное поле. Заполните его!');
            } else {
                $this->xml->text($value['value']);
            }
            $this->xml->endElement();
        }
        $this->xml->endElement();
    }

    public function writeCategories(array $categories): void
    {
        // TODO: сделать здесь вложенность
        $this->xml->startElement('categories');
        if (count($categories)) {
            /** @var Category $category */
            foreach ($categories as $category) {
                if ($resource = $category->getResource()) {
                    $this->writeCategory($resource);
                }
            }
        } else {
            $this->xml->writeComment('Если не выбрать категории - то все товары будут выгружены');
        }
        $this->xml->endElement();
    }

    protected function writeCategory(modResource $category): void
    {
        $this->xml->startElement('category');
        $this->xml->writeAttribute('id', $category->get('id'));
        $this->xml->text($category->get('pagetitle'));
        $this->xml->endElement();
    }

    public function writeOffers(array $offers): void
    {
    }

    public function writeOffer(Offer $offer, Pricelist $pricelist): void
    {
        if ($field = $pricelist->getFieldByType(Field::TYPE_OFFER)) {
            $this->writeOfferField($offer, $field);
        } else {
            $this->writeComment('Cannot find field with type = '.Field::TYPE_OFFER);
        }
    }

    protected function writeOfferField(Offer $offer, Field $field): void
    {
        $this->xml->startElement($field->name);

        if ($attributes = $field->getAttributes()) {
            foreach ($attributes as $attribute) {
                $this->writeOfferAttribute($offer, $attribute);
            }
        }

        switch ($field->type) {
            case Field::TYPE_PARENT:
            case Field::TYPE_OFFER:
                foreach ($field->getChildren() as $child) {
                    $this->writeOfferField($offer, $child);
                }
                break;
            case Field::TYPE_VALUE:
            case Field::TYPE_CDATA_VALUE:
                if (isset($field->value)) {
                    $value = $offer->get($field->value);
                }
                if (!isset($value) && isset($field->handler)) {
                    $value = $field->handler; // TODO: тут обработка Fenom из pdoTools
                }
                if (isset($value) && $value !== '') {
                    if ($field->type === Field::TYPE_CDATA_VALUE) {
                        $this->xml->writeCdata(($this->jevix ? $this->jevix->parse($value, $this->errors) : $value));
                    } else {
                        $this->xml->text($value);
                    }
                } else {
                    $this->writeComment('Empty value for offer id='.$offer->get('id'));
                }
                break;
            case Field::TYPE_PICTURES:
                $this->writeComment('Need to be implemented');
                break;
        }

        $this->xml->endElement();
    }

    protected function writeOfferAttribute(Offer $offer, Attribute $attribute): void
    {
        // TODO: добавить обработку значения через handler, если присутствует
        switch ($attribute->getType()) {
            case Attribute::TYPE_TEXT:
                if (!empty($attribute->value)) {
                    $this->xml->writeAttribute($attribute->name, $attribute->value);
                }
                break;
            default:
                if ($value = $offer->get($attribute->value)) {
                    $this->xml->writeAttribute($attribute->name, $value);
                }
                break;
        }
    }

    public function writeComment(string $comment, bool $indentAfter = true): void
    {
        $this->xml->writeComment($comment);
        if ($indentAfter) {
            $this->xml->setIndent(true);
        }
    }

    public function openFile(string $path): bool
    {
        return $this->xml->openUri($path);
    }

    public function closeDocument(): void
    {
        $this->xml->endDocument();
        $this->xml->flush();
    }

    public function setPreviewMode(): PricelistWriter
    {
        $this->xml->openMemory();
        return $this;
    }

    public function getPreviewXml(): string
    {
        return $this->xml->outputMemory(true);
    }

    public function xml(): XMLWriter
    {
        return $this->xml;
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
            $this->errors[] = $exception->getMessage();
        }
    }

    public function writeField(Field $field, array $pls = []): void
    {
        switch ($field->type) {
            case Field::TYPE_TEXT:
            case Field::TYPE_VALUE:
            case Field::TYPE_CDATA_VALUE:
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
                    $this->writeField($child, $pls);
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
     */
    protected function writeValuableField(Field $field, array $pls = []): void
    {
        $value = null;

        switch ($field->type) {
            case Field::TYPE_TEXT:
                $value = $field->value;
                break;
            case Field::TYPE_VALUE:
            case Field::TYPE_CDATA_VALUE:
                $value = $pls[$field->value] ?? $field->value; // TODO: тут из объекта взять значение (или это будет в pls)
                $value = $this->prepareValue($value, $field->handler, $pls);
                break;
        }
        if ($value === '' || $value === null) {
            //пустые значения пропускаются, даже если у них есть атрибуты
            return;
        }

        $this->xml->startElement($field->name);

        $this->writeAttributes($field->getAttributes(), $pls);

        if ($field->type === Field::TYPE_CDATA_VALUE) {
            $this->xml->writeCdata($value);
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
                $value = $pls[$attribute->value] ?? $attribute->value; // TODO: тут из объекта взять значение (или это будет в pls)
                $value = $this->prepareValue($value, $attribute->handler, $pls);
                break;
            default:
                $this->errorLog("Undefined type {$attribute->getType()} for attribute {$attribute->name} ({$attribute->id})");
        }

        if ($value !== null && $value !== '') {
            $this->xml->writeAttribute($attribute->name, $value);
        }
    }

    protected function prepareValue(?string $value, ?string $handler, array $pls = []): ?string
    {
        if (!empty($handler) && $this->pdoTools) {
            if (mb_stripos(trim($handler), '@INLINE') !== 0) {
                $handler = '@INLINE '.trim($handler);
            }
            $value = $this->pdoTools->getChunk($handler, array_merge($pls, ['input' => $value]), true);
        }
        return $value;
    }

    protected function initializePdoTools(): bool
    {
        $pdoTools = $this->pricelist->modX()->getService('pdoTools');
        if ($pdoTools && $pdoTools instanceof pdoTools) {
            $this->pdoTools = $pdoTools;
            return true;
        }
        return false;
    }

    protected function writeCurrenciesField(Field $field, array $pls = []): void
    {
        if (!empty($field->value)) {
            $currencies = json_decode($field->value, true);
            if (!empty($currencies)) {
                $this->xml->startElement($field->name);
                $this->writeAttributes($field->getAttributes(), $pls);
                foreach ($currencies as $i => $currency) {
                    $this->xml->startElement($field->getProperties()['child'] ?? 'currency');
                    $this->xml->writeAttribute('id', $currency);
                    if (!$i) {
                        $this->xml->writeAttribute($field->getProperties()['rate'] ?? 'rate',
                            $field->getProperties()['rate_value'] ?? '1');
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

        $categories = $this->pricelist->getCategories();
        if (empty($categories)) {
            //TODO: тут надо получить все категории товаров, которые участвуют в выборке
        }
        foreach ($categories as $category) {
            $this->writeCategoryTree($category, $field);
        }
        $this->xml->endElement();
    }

    protected function writeCategoryTree(Category $category, Field $fieldCategories): void
    {
        /** @var modResource $resource */
        $resource = $category->getResource();
        $this->xml->startElement($fieldCategories->getProperties()['child'] ?? 'category');
        $this->xml->writeAttribute($fieldCategories->getProperties()['id_attribute'] ?? 'id', $resource->get('id'));
        if ($parentId = $resource->get('parent')) {
            $this->xml->writeAttribute($fieldCategories->getProperties()['parent_attribute'] ?? 'parentId', $parentId);
        }
        $this->xml->text($resource->get($fieldCategories->getProperties()['resource_column'] ?? 'pagetitle')); //тут формулу нужно
        $this->xml->endElement();
    }

    protected function writeOffersField(Field $field, array $pls = []): void
    {
        // TODO: тут нужно получить все товары по условия из прайс-листа
        $this->xml->startElement($field->name);
        $this->writeAttributes($field->getAttributes(), $pls);
        $this->xml->writeComment('Элемент offers обязательный');

        $q = $this->pricelist->queryForOffers();
        $offers = $this->pricelist->modx()->getIterator($q->getClass(), $q);

        if (($children = $field->getChildren()) && $offerField = reset($children)) {
            foreach ($offers as $offer) {
                /** @var Offer $offer */
                $this->writeField($offerField, array_merge($pls, $offer->toArray(), [
                    'Offer'       => $offer,
                    'Data'        => $offer,
                    'Resource'    => $offer,
                    'modResource' => $offer,
                ])); //TODO: переделать
            }
        } else {
            $this->errorLog("Empty children for field {$field->name} ({$field->id})");
        }

        $this->xml->endElement();
    }

}