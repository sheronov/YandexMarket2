<?php

use YandexMarket\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistGetProcessor extends modObjectGetProcessor
{
    public $objectType     = 'ymPricelist';
    public $classKey       = 'ymPricelist';
    public $languageTopics = ['yandexmarket2:default'];

    public function cleanup()
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray());
    }
}

return ymPricelistGetProcessor::class;