<?php

namespace YandexMarket\Models;

use InvalidArgumentException;
use xPDO;
use xPDOObject;

abstract class BaseObject
{
    protected $xpdo;
    protected $object;

    public function __construct(xPDO $xpdo, xPDOObject $object = null)
    {
        $objectClass = static::getObjectClass();
        /** @var xPDOObject $newObj */
        if (!$object && $newObj = $xpdo->newObject($objectClass)) {
            $object = $newObj;
        }
        if (!$object || !is_a($object, $objectClass)) {
            throw new InvalidArgumentException("You should provide ".$objectClass.' entity');
        }
        $this->object = $object;
        $this->xpdo = $xpdo;
    }

    abstract public static function getObjectClass(): string;

    public function getObject(): ?xPDOObject
    {
        return $this->object;
    }

}