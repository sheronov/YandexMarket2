<?php

namespace YandexMarket\Models;

use modResource;
use ymCategory;

/**
 * @property int $id
 * @property int $resource_id
 * @property int $pricelist_id
 * @property null|string $name
 * @property null|array $properties
 */
class Category extends BaseObject
{
    /** @var null|modResource $resource */
    protected $resource;

    protected $pricelist;

    public static function getObjectClass(): string
    {
        return ymCategory::class;
    }

    public function setPricelist(Pricelist $pricelist): Category
    {
        $this->pricelist = $pricelist;
        return $this;
    }

    public function getPricelist(): Pricelist
    {
        if (!isset($this->pricelist)) {
            $this->pricelist = new Pricelist($this->modx, $this->object->getOne('Pricelist'));
        }

        return $this->pricelist;
    }

    public function setResource(modResource $resource): Category
    {
        $this->resource = $resource;
        return $this;
    }

    public function getResource(): ?modResource
    {
        if (!isset($this->resource)) {
            $this->resource = $this->object->getOne('Resource');
        }

        return $this->resource;
    }
}