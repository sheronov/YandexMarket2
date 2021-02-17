<?php

namespace YandexMarket\Models;

use ymFieldAttribute;

/**
 * @property int $id
 * @property string $name
 * @property int $field_id
 * @property null|string $value
 * @property null|string $handler
 * @property null|array $properties
 */
class Attribute extends BaseObject
{
    public const TYPE_STRING  = 0;
    public const TYPE_DATE    = 1;
    public const TYPE_BOOLEAN = 2;
    public const TYPE_RAW     = 3;

    public const TYPE_DEFAULT = self::TYPE_STRING;

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

    public function getType(): int
    {
        return $this->properties['type'] ?? self::TYPE_DEFAULT;
    }

    public function getProperties(): array
    {
        return $this->properties ?? [];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['type'] = $this->getType();
        $data['properties'] = $this->getProperties();
        $data['label'] = $this->getLabel();
        if ($values = $this->getProperties()['values'] ?? []) {
            $data['values'] = array_map(function ($value) {
                return [
                    'value' => $value,
                    'text'  => $this->getLexicon($this->lexiconKey().'_value_'.$value) ?? $value
                ];
            }, $values);
        }
        return $data;
    }

    public function getLabel(): string
    {
        $label = $this->getLexicon($this->lexiconKey()) ?? $this->name;

        if ($this->getProperties()['required'] ?? false) {
            $label .= ' *';
        }

        return $label;
    }

    public function lexiconKey(): string
    {
        return "ym_{$this->getField()->getPricelist()->type}_{$this->getField()->name}_attr_{$this->name}";
    }

    public function getLexicon(string $key): ?string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        return null;
    }
}
