<?php

namespace YandexMarket\Model;

use xPDO\Om\xPDOSimpleObject;

class YmCondition extends xPDOSimpleObject
{
    use PricelistTouch;

    public function getField($key, $validate = false)
    {
        return parent::getField($key === 'name' ? 'column' : $key, $validate);
    }

    public function get($k, $format = null, $formatTemplate = null)
    {
        return parent::get($k === 'name' ? 'column' : $k, $format, $formatTemplate);
    }

}
