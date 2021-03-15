<?php

use YandexMarket\Models\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistGetProcessor extends modObjectGetProcessor
{
    public $objectType     = 'ym2_pricelist';
    public $classKey       = ymPricelist::class;
    public $languageTopics = ['yandexmarket2:default'];

    public function cleanup()
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray(true));
    }
}

return ymPricelistGetProcessor::class;