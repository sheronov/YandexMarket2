<?php

namespace YandexMarket\Processors\Attributes;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmFieldAttribute;

class Remove extends RemoveProcessor
{
    public $objectType     = 'ym2_attribute';
    public $classKey       = YmFieldAttribute::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

}
