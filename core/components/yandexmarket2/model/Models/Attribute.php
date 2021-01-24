<?php

namespace YandexMarket\Models;

use ymFieldAttribute;

class Attribute extends BaseObject
{

    public static function getObjectClass(): string
    {
        return ymFieldAttribute::class;
    }
}