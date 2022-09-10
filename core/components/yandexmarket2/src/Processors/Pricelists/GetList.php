<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AGetList extends \modObjectGetListProcessor
    {
        public $classKey = \YmPricelist::class;

        public function prepareRow(\xPDOObject $object): array
        {
            return static::preparePricelist($object);
        }
    }
} else {
    abstract class AGetList extends GetListProcessor
    {
        public $classKey = YmPricelist::class;

        public function prepareRow(xPDOObject $object): array
        {
            return static::preparePricelist($object);
        }
    }
}

class GetList extends AGetList
{
    public $objectType           = 'ym2_pricelist';
    public $defaultSortField     = 'id';
    public $defaultSortDirection = 'DESC';

    protected function preparePricelist($object): array
    {
        return (new Pricelist($this->modx, $object))->toArray();
    }
}