<?php

namespace YandexMarket\Xml;

use Exception;
use Jevix;
use modResource;
use XMLWriter;
use YandexMarket\Models\Attribute;
use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;

class PricelistWriter
{
    protected $xml;
    protected $errors = [];
    protected $jevix  = null;

    public function __construct()
    {
        $this->initializeJevix();
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
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
                        $this->xml->writeCdata($this->jevix ? $this->jevix->parse($value, $this->errors) : $value);
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

    public function getXml(): string
    {
        return $this->xml->outputMemory(true);
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

}