<?php

namespace YandexMarket\Models;

use miniShop2;
use modResource;
use msProduct;
use xPDOObject;
use YandexMarket\Service;

/**
 * @package YandexMarket\Models
 * @property msProduct|modResource|xPDOObject $object
 */
class Offer extends BaseObject
{
    /** @var Pricelist */
    protected $pricelist;

    // под оффером может быть не только modResource, а любой XPDO object
    public static function getObjectClass(): string
    {
        return xPDOObject::class;
    }

    public function setPricelist(Pricelist $pricelist): Offer
    {
        $this->pricelist = $pricelist;
        return $this;
    }

    public function get(string $field)
    {
        if (mb_strpos($field, '.') !== false) {
            list($class, $key) = explode('.', $field, 2);
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
                    $field = $key;
                    break;
                case 'data':
                case 'msproductdata':
                    // возможно лучше Data.
                    $field = $key;
                    break;
                case 'vendor':
                case 'msvendor':
                    $field = 'vendor.'.$key;
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
                case 'msgallery':
                case 'msproductfile':
                    $field = 'msgallery.'.$key;
                    break;
                case 'category':
                case 'parent':
                    $field = 'category.'.$key;
                    break;
                case 'categorytv':
                case 'parenttv':
                    $field = 'categorytv.'.$key;
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
        }

        return $this->object->_fields[$field] ?? parent::get($field);
    }

    /***
     * Подумать над тем, чтобы сюда подставлять URL из настроек прайс-листа
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->modx->makeUrl($this->object->get('id'), $this->object->get('context_key'), '', 'full');
    }

    public function getImage(): string
    {
        if ($image = $this->object->get('image')) {
            if (mb_strpos($image, '//') === false) {
                $image = rtrim($this->modx->getOption('yandexmarket2_site_url', null,
                        $this->modx->getOption('site_url')), '/').'/'.ltrim($image,'/');
            }
        } else {
            $image = '';
        }
        return $image;
    }

    public function getPrice(): float
    {
        $price = (float)$this->object->get('price');
        if (!Service::hasMiniShop2()) {
            return $price;
        }

        $ctx = $this->modx->context->key; // change ctx for plugins work
        $this->modx->context->key = $this->object->get('context_key');

        /** @var miniShop2 $miniShop2 */
        if ($miniShop2 = $this->modx->getService('miniShop2')) {
            $params = [
                'product' => $this->object,
                'data'    => $this->object->toArray(),
                'price'   => $price,
            ];
            $response = $miniShop2->invokeEvent('msOnGetProductPrice', $params);
            if ($response['success']) {
                $price = $response['data']['price'];
                if ($price < $params['price']) {
                    $this->object->set('old_price', $params['price']);
                }
            }
        }

        $this->modx->context->key = $ctx;
        return $price;
    }

}