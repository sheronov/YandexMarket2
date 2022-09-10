<?php

namespace YandexMarket\Processors\Conditions;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use YandexMarket\Model\YmCondition;
use YandexMarket\Models\Condition;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AUpdate extends \modObjectUpdateProcessor
    {
        public $classKey = \YmCondition::class;
    }
} else {
    abstract class AUpdate extends UpdateProcessor
    {
        public $classKey = YmCondition::class;
    }
}

class Update extends AUpdate
{
    public $objectType     = 'ym2_condition';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $column = trim($this->getProperty('column'));
        $operator = trim($this->getProperty('operator'));
        if (empty($column)) {
            $this->modx->error->addField('column', $this->modx->lexicon('ym2_condition_column_err_ns'));
        } elseif (empty($operator) || !array_key_exists($operator, Condition::OPERATOR_SYMBOLS)) {
            $this->modx->error->addField('operator', $this->modx->lexicon('ym2_condition_operator_err_ns'));
        }

        if (in_array($operator, ['exists in', 'not exists in'], true)) {
            $this->setProperty('value', json_encode($this->getProperty('value'), JSON_UNESCAPED_UNICODE));
        } elseif (in_array($operator, ['is null', 'is not null'], true)) {
            $this->setProperty('value', null);
        }

        return parent::beforeSet();
    }

    public function cleanup()
    {
        return $this->success('', (new Condition($this->modx, $this->object))->toArray());
    }
}
