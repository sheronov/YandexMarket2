<?php

namespace YandexMarket\Processors\Fields;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmField;

class Remove extends RemoveProcessor
{
    public $objectType     = 'ym2_field';
    public $classKey       = YmField::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

}
