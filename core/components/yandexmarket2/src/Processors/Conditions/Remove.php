<?php
namespace YandexMarket\Processors\Conditions;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmCondition;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ARemove extends \modObjectRemoveProcessor
    {
        public $classKey = \YmCondition::class;
    }
} else {
    abstract class ARemove extends RemoveProcessor
    {
        public $classKey = YmCondition::class;
    }
}

class Remove extends ARemove
{
    public $objectType     = 'ym2_condition';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';
}
