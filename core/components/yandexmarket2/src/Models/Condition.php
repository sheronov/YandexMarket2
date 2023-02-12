<?php

namespace YandexMarket\Models;

/**
 * @property int $id
 * @property int $pricelist_id
 * @property string $column
 * @property string $operator
 * @property null|string $value
 * @property string $group
 * @property null|array $properties
 */
class Condition extends BaseObject
{
    // https://docs.modx.com/current/en/extending-modx/xpdo/class-reference/xpdoquery/xpdoquery.where
    const OPERATOR_SYMBOLS = [
        'equals'                   => null,
        'not equals'               => '!=',
        'greater than'             => '>',
        'less than'                => '<',
        'greater than or equal to' => '>=',
        'less than or equal to'    => '<=',
        'like'                     => 'LIKE',
        'not like'                 => 'NOT LIKE',
        'exists in'                => 'IN',
        'not exists in'            => 'NOT IN',
        'is null'                  => 'IS NULL',
        'is not null'              => 'IS NOT NULL',
    ];
    const GROUP_OFFER      = 'offer';
    const GROUP_CATEGORY   = 'category';

    /** @var Pricelist */
    protected $pricelist;

    public static function getObjectClass(): string
    {
        return MODX3 ? \YandexMarket\Model\YmCondition::class : \YmCondition::class;
    }

    public function getPricelist(): Pricelist
    {
        if (!isset($this->pricelist)) {
            $this->pricelist = new Pricelist($this->modx, $this->object->getOne('Pricelist'));
        }
        return $this->pricelist;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        if (in_array($this->operator, ['exists in', 'not exists in'], true)) {
            $data['value'] = !empty($this->value)
                ? json_decode($this->value, true, 512, JSON_UNESCAPED_UNICODE)
                : [];
        }
        return $data;
    }

    public function valueToWhere () {
        switch ($this->operator) {
            case 'exists in':
            case 'not exists in':
                return json_decode($this->value, true, 512, JSON_UNESCAPED_UNICODE);
            case 'is null':
            case 'is not null':
                return null;
            default:
                return $this->value;
        }
    }

    public function addonToWhere (): string
    {
        $operator = self::OPERATOR_SYMBOLS[$this->operator];

        switch ($this->operator) {
            case 'is null':
            case 'is not null':
                $operator = $this->operator === 'is null' ? 'IS' : 'IS NOT';
                break;
        }
        if ($operator === null) {
            $operator = '';
        } else {
            $operator = ':'.$operator;
        }

        return $operator;
    }

}
