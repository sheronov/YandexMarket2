<?php

require_once dirname(__DIR__, 2).'/src/Model/PricelistTouch.php';

class YmCondition extends xPDOSimpleObject
{
    use \YandexMarket\Model\PricelistTouch;

    public function getField($key, $validate = false)
    {
        return parent::getField($key === 'name' ? 'column' : $key, $validate);
    }

    public function get($k, $format = null, $formatTemplate = null)
    {
        return parent::get($k === 'name' ? 'column' : $k, $format, $formatTemplate);
    }

}