<?php

namespace YandexMarket\Models;

use modResource;
use ymCategory;

/**
 * @property int $id
 * @property int $resource_id
 * @property int $pricelist_id
 * @property null|string $name
 */
class Category extends BaseObject
{

    public static function getObjectClass(): string
    {
        return ymCategory::class;
    }

    public function getPricelist(): Pricelist
    {
        return new Pricelist($this->xpdo, $this->object->getOne('Pricelist'));
    }

    public function getResource(): ?modResource
    {
        /** @var null|modResource $resource */
        $resource = $this->object->getOne('Resource');
        return $resource;
    }
}