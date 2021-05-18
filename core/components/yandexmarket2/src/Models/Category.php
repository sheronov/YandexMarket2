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

    public function get(string $field)
    {
        if (mb_strpos($field, '.') !== false) {
            list($class, $key) = explode('.', $field, 2);
            switch (mb_strtolower($class)) {
                case 'category':
                    $getMethod = 'get'.ucfirst($key);
                    if (method_exists($this, $getMethod)) {
                        //getSomething()
                        return $this->$getMethod();
                    }
                    if (method_exists($this->object, $getMethod)) {
                        return $this->object->$getMethod();
                    }
                    $field = $key;
                    break;
                case 'resource':
                case 'modresource':
                    $field = $key;
                    break;
                case 'tv':
                case 'modtemplatevar':
                case 'modtemplatevarresource':
                    $field = 'tv.'.$key;
                    break;
                case 'option':
                case 'msoption';
                case 'msproductoption';
                    $field = 'option.'.$key;
                    break;
                case 'ms2gallery':
                case 'msresourcefile':
                    $field = 'ms2gallery.'.$key;
                    break;
                case 'setting':
                    return $this->modx->getOption($field); //можно даже в полях указывать Setting.some_setting
                case 'pricelist':
                    return $this->pricelist->get($field);
            }
        } elseif ($resource = $this->getResource()) {
            return $resource->get($field);
        }

        return $this->resource->_fields[$field] ?? $this->object->_fields[$field] ?? parent::get($field);
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

    /**
     * @return modResource|null
     */
    public function getResource()
    {
        if (!isset($this->resource)) {
            $this->resource = $this->object->getOne('Resource');
        }
        if ($this->resource && !$this->resource->parent) {
            $this->resource->parent = null;
        }

        return $this->resource;
    }
}