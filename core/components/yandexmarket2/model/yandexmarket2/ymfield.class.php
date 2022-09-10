<?php

require_once dirname(__DIR__, 2).'/src/Model/PricelistTouch.php';

class YmField extends xPDOSimpleObject
{
    use \YandexMarket\Model\PricelistTouch;
}