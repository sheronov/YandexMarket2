<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmPricelist;

class Remove extends RemoveProcessor
{
    public $objectType     = 'ym2_pricelist';
    public $classKey       = YmPricelist::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';
}
