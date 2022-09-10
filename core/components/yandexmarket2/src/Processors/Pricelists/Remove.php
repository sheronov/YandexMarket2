<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ARemove extends \modObjectRemoveProcessor
    {
        public $classKey = \YmPricelist::class;
    }
} else {
    abstract class ARemove extends RemoveProcessor
    {
        public $classKey = YmPricelist::class;
    }
}

class Remove extends ARemove
{
    public $objectType     = 'ym2_pricelist';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';
}
