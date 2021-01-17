<?php

namespace YandexMarket;

use ymPricelist;

class Pricelist extends BaseObject
{
    /** @var ymPricelist $object */

    public const OBJECT_CLASS = ymPricelist::class;

    public function toArray(): array
    {
        $data = $this->object->toArray();
        $data['fields'] = [
            ['root', 'shop']
        ];

        return $data;
    }

    public function getCategories(): array
    {
        return $this->object->getMany('Categories');
    }

}