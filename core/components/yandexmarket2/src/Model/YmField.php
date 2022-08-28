<?php

namespace YandexMarket\Model;

use xPDO\Om\xPDOSimpleObject;
use xPDO\xPDO;

class YmField extends xPDOSimpleObject
{
    use PricelistTouch;

    public static function load(xPDO &$xpdo, $className, $criteria, $cacheFlag = true)
    {
        return parent::load($xpdo, $className, $criteria, $cacheFlag);
    }
}
