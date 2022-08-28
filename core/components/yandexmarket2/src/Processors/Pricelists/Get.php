<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\GetProcessor;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;

class Get extends GetProcessor
{
    public $objectType     = 'ym2_pricelist';
    public $classKey       = YmPricelist::class;
    public $languageTopics = ['yandexmarket2:default'];

    public function cleanup()
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray(true));
    }
}
