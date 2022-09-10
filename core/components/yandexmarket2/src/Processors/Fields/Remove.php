<?php

namespace YandexMarket\Processors\Fields;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmField;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ARemove extends \modObjectRemoveProcessor
    {
        public $classKey = \YmField::class;
    }
} else {
    abstract class ARemove extends RemoveProcessor
    {
        public $classKey = YmField::class;
    }
}

class Remove extends ARemove
{
    public $objectType     = 'ym2_field';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

}
