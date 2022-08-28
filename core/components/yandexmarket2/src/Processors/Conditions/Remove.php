<?php
namespace YandexMarket\Processors\Conditions;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmCondition;

class Remove extends RemoveProcessor
{
    public $objectType     = 'ym2_condition';
    public $classKey       = YmCondition::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';
}
