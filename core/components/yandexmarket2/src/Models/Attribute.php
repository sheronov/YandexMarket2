<?php

namespace YandexMarket\Models;

use ymFieldAttribute;

/**
 * @property int $id
 * @property string $name
 * @property int $field_id
 * @property int $type
 * @property null|string $value
 * @property null|string $handler
 * @property null|array $properties
 */
class Attribute extends BaseObject
{
    const TYPE_TEXT  = 0;
    const TYPE_VALUE = 1;

    const TYPE_DEFAULT = self::TYPE_TEXT;

    protected $field;

    public static function getObjectClass(): string
    {
        return ymFieldAttribute::class;
    }

    public function setField(Field $field): Attribute
    {
        $this->field = $field;
        return $this;
    }

    public function getField(): Field
    {
        if (!isset($this->field)) {
            $this->field = new Field($this->modx, $this->object->getOne('Field'));
        }
        return $this->field;
    }

    public function getProperties(): array
    {
        return $this->properties ?? [];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['properties'] = $this->getProperties();
        $data['label'] = $this->getLabel();
        if ($values = $this->getProperties()['values'] ?? []) {
            $data['values'] = array_map(function ($value) {
                return [
                    'value' => $value,
                    'text'  => $this->getLexicon($this->lexiconKey().'_value_'.$value) ?: $value
                ];
            }, $values);
        }
        return $data;
    }

    public function getLabel(): string
    {
        $label = $this->getLexicon($this->lexiconKey()) ?: $this->name;

        if ($this->getProperties()['required'] ?? false) {
            $label .= ' *';
        }

        return $label;
    }

    public function lexiconKey(): string
    {
        return "ym2_{$this->getField()->getPricelist()->type}_{$this->getField()->name}_attr_{$this->name}";
    }

    public function getLexicon(string $key): string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        return '';
    }
}
