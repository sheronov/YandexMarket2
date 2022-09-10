<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\GetProcessor;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AGet extends \modObjectGetProcessor
    {
        public $classKey = \YmPricelist::class;
    }
} else {
    abstract class AGet extends GetProcessor
    {
        public $classKey = YmPricelist::class;
    }
}

class Get extends AGet
{
    public $objectType     = 'ym2_pricelist';
    public $languageTopics = ['yandexmarket2:default'];

    public function cleanup()
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray(true));
    }
}
