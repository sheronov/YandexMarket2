<?php

use YandexMarket\Models\Condition;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymConditionUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'ym2_condition';
    public $classKey       = ymCondition::class;
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

return ymConditionUpdateProcessor::class;
