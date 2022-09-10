<?php

namespace YandexMarket\Processors\Attributes;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmFieldAttribute;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ARemove extends \modObjectRemoveProcessor
    {
        public $classKey = \YmFieldAttribute::class;
    }
} else {
    abstract class ARemove extends RemoveProcessor
    {
        public $classKey = YmFieldAttribute::class;
    }
}

class Remove extends ARemove
{
    public $objectType     = 'ym2_attribute';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

}
