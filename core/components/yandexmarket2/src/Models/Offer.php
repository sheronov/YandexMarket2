<?php

namespace YandexMarket\Models;

use modResource;

class Offer extends BaseObject
{

    public static function getObjectClass(): string
    {
        return modResource::class;
    }
}