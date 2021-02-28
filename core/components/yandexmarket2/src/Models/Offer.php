<?php

namespace YandexMarket\Models;

use miniShop2;
use modResource;
use xPDOObject;
use YandexMarket\Service;

class Offer extends BaseObject
{
    public static function getObjectClass(): string
    {
        return modResource::class;
    }

    /**
     * @return modResource|xPDOObject
     */
    public function getResource(): modResource
    {
        return $this->object;
    }

    public function getPictures(): array
    {
        $pictures = [];// TODO: сюда должны попадать все изображения, который приджойнены из поля
        if ($image = $this->get('Data.image')) {
            $pictures[] = Service::preparePath($this->modx, '{site_url}'.$image, true);
        }
        return $pictures;
    }

    public function getLoadedOptions(): array
    {
        return []; // TODO: сюда должны попадать значения приджойненных опций
    }

    public function getLoadedTVs(): array
    {
        return []; // TODO: сюда должны попадать значения приджойненных ТВ-шек
    }

    public function get(string $field)
    {
        if (mb_strpos($field, '.') !== false) {
            [$class, $key] = explode('.', $field, 2);
            switch (mb_strtolower($class)) {
                case 'offer': //it's like proxy class
                    $getMethod = 'get'.ucfirst($key);
                    if (method_exists($this, $getMethod)) {
                        //getUrl(), getPrice()
                        return $this->$getMethod();
                    }
                    if (method_exists($this->object, $getMethod)) {
                        return $this->object->$getMethod();
                    }
                    $field = $key;
                    break;
                case 'resource':
                case 'modresource':
                case 'product':
                case 'msproduct':
                case 'data':
                case 'msproductdata':
                    $field = $key;
                    break;
                case 'vendor':
                case 'msvendor':
                    $field = 'vendor.'.$key;
                    break;
            }
        }

        return parent::get($field);
    }

    public function getUrl(): string
    {
        return $this->modx->makeUrl($this->object->get('id'), $this->object->get('context'), '', 'full');
    }

    public function getPrice(): float
    {
        $price = (float)$this->object->get('price');
        if (!Service::hasMiniShop2()) {
            return $price;
        }

        /** @var miniShop2 $miniShop2 */
        if ($miniShop2 = $this->modx->getService('miniShop2')) {
            $params = [
                'product' => $this->object,
                'data'    => $this->object->toArray(),
                'price'   => $price,
            ];
            $response = $miniShop2->invokeEvent('msOnGetProductPrice', $params);
            if ($response['success']) {
                $price = $params['price'] = $response['data']['price'];
            }
        }

        return $price;
    }

}