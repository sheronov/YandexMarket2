<?php

use YandexMarket\Models\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistGetListProcessor extends modObjectGetListProcessor
{
    public $objectType           = 'ymPricelist';
    public $classKey             = 'ymPricelist';
    public $defaultSortField     = 'id';
    public $defaultSortDirection = 'DESC';

    public function prepareRow(xPDOObject $object): array
    {
        return (new Pricelist($this->modx, $object))->toArray();
    }

}

return ymPricelistGetListProcessor::class;