<?php

namespace YandexMarket\Handlers;

use YandexMarket\Models\Attribute;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

class PricelistFiller
{
    protected $pricelist;
    protected $marketplace;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->marketplace = $pricelist->getMarketplace();
    }

    public function fillDefaultConditions(): void
    {
        $this->pricelist->newCondition('published', 'equals', 1)->save();
        $this->pricelist->newCondition('deleted', 'equals', 0)->save();

        if (Service::hasMiniShop2()) {
            $this->pricelist->newCondition('class_key', 'equals', 'msProduct')->save();
        }
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
            $field = $this->pricelist->newField($name, $data['type'] ?? Field::TYPE_DEFAULT);
            $field->parent = $parent->id ?? null;
            $field->rank = ++$rank;
            if ($properties = array_filter($data, static function (string $key) {
                return !in_array($key, ['type', 'fields'], true);
            }, ARRAY_FILTER_USE_KEY)) {
                $field->properties = $properties;
            }
            if ($parent && $value = $this->marketplace->defaultValues()[$parent->type][$name] ?? null) {
                if (is_array($value) && array_values($value) !== $value) {
                    if (isset($value['handler'])) {
                        $field->handler = $value['handler'];
                    }
                    if (isset($value['value'])) {
                        $field->value = is_array($value['value']) ? json_encode($value['value']) : $value['value'];
                    }
                } else {
                    $field->value = is_array($value) ? json_encode($value) : $value;
                }
            }
            $field->save();

            if ($parent) {
                $parent->addChildren($field);
            }

            if ($attributes = array_filter($data['attributes'] ?? [], static function (array $attribute) {
                return $attribute['required'] ?? false;
            })) {
                foreach ($attributes as $attrName => $attrData) {
                    $attribute = $field->newAttribute($attrName);
                    if (isset($this->marketplace->defaultAttributes()[$field->type][$attrName])) {
                        $attribute->value = $this->marketplace->defaultAttributes()[$field->type][$attrName];
                    }
                    $attribute->type = $attrData['type'] ?? Attribute::TYPE_DEFAULT;
                    $attribute->properties = $attrData;
                    $attribute->save();
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
        }

        return $fields;
    }
}