<?php

namespace YandexMarket\Handlers;

use DateTimeImmutable;
use YandexMarket\Models\Attribute;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class PricelistFiller
{
    protected $pricelist;
    protected $modx;
    protected $marketplace;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->modx = $pricelist->modX();
        $this->marketplace = $pricelist->getMarketplace();
    }

    public function fillDefaultValues(): array
    {
        $rootFields = $this->marketplace::getRootFields();

        return $this->createFields($rootFields, null);
    }

    protected function createFields(array $fields, ?Field $parent): array
    {
        $fields = array_filter($fields, function (array $field, string $name) use ($parent) {
            return ($field['required'] ?? false) //обязательно к добавлению
                || in_array($field['type'] ?? null,
                    [Field::TYPE_OFFER, Field::TYPE_SHOP, Field::TYPE_ROOT, Field::TYPE_CATEGORIES], true)
                || ($this->marketplace->defaultValues()[$parent->type ?? null][$name] ?? null); //если дефолтные значения
        }, ARRAY_FILTER_USE_BOTH);

        $rank = 0;
        foreach ($fields as $name => $data) {
            $field = new Field($this->modx);
            $field->name = $name;
            $field->parent = $parent->id ?? null;
            $field->type = $data['type'] ?? Field::TYPE_DEFAULT;
            $field->pricelist_id = $this->pricelist->id;
            $field->rank = $rank;
            $field->created_on = new DateTimeImmutable();
            $field->active = true;
            if ($properties = array_filter($data, static function (string $key) {
                return !in_array($key, ['type', 'fields', 'attributes'], true);
            }, ARRAY_FILTER_USE_KEY)) {
                $field->properties = $properties;
            }
            if ($parent && $value = $this->marketplace->defaultValues()[$parent->type][$name] ?? null) {
                $field->value = is_array($value) ? json_encode($value) : $value;
            }
            $field->save();

            if ($parent) {
                $parent->addChildren($field);
            }

            if ($attributes = $this->marketplace->defaultAttributes()[$field->type] ?? []) {
                foreach ($attributes as $attrName => $attrValue) {
                    $attribute = new Attribute($this->modx);
                    $attribute->name = $attrName;
                    $attribute->value = $attrValue;
                    $attribute->field_id = $field->id;
                    if ($attribute->save()) {
                        $field->addAttribute($attribute);
                    }
                }
            }

            if ($children = $data['fields'] ?? []) {
                $this->createFields($children, $field);
            } elseif ($field->type === Field::TYPE_OFFER) {
                $this->createFields($this->marketplace::getOfferFields(), $field);
            } elseif ($field->type === Field::TYPE_SHOP) {
                $this->createFields($this->marketplace::getShopFields(), $field);
            }

            $fields[$name] = $field;
            $rank += 10; //чтобы удобнее было перемещать
        }

        return $fields;
    }
}