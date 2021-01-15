<?php

namespace YandexMarket;

use InvalidArgumentException;
use xPDO;
use xPDOObject;

abstract class BaseObject
{
    public const OBJECT_CLASS = xPDOObject::class;

    protected $xpdo;
    protected $object;

    public function __construct(xPDO $xpdo, xPDOObject $object = null)
    {
        /** @var xPDOObject $newObj */
        if (!$object && $newObj = $xpdo->newObject(static::OBJECT_CLASS)) {
            $object = $newObj;
        }
        if (!$object || !is_a($object, static::OBJECT_CLASS)) {
            throw new InvalidArgumentException("You should provide ".static::OBJECT_CLASS.' entity');
        }
        $this->object = $object;
        $this->xpdo = $xpdo;
    }

    public function getObject(): ?xPDOObject
    {
        return $this->object;
    }

}