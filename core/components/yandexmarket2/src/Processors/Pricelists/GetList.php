<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;

class GetList extends GetListProcessor
{
    public $objectType           = 'ym2_pricelist';
    public $classKey             = YmPricelist::class;
    public $defaultSortField     = 'id';
    public $defaultSortDirection = 'DESC';

    public function prepareRow(xPDOObject $object): array
    {
        return (new Pricelist($this->modx, $object))->toArray();
    }

    public function afterIteration(array $list): array
    {
        // add here some usable information
        return parent::afterIteration($list);
    }

}
