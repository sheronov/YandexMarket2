<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use xPDO;
use xPDOObject;

abstract class BaseObject
{
    protected $xpdo;
    protected $object;

    protected const DATETIME_FIELDS = ['created_on', 'edited_on', 'generated_on'];
    protected const ARRAY_FIELDS    = ['properties'];

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

    /**
     * @param $name
     *
     * @return array|DateTimeImmutable|mixed|xPDOObject|null
     * @throws Exception
     */
    public function __get($name)
    {
        if ($value = $this->object->$name) {
            if (in_array($name, self::ARRAY_FIELDS, true)) {
                $value = json_decode($value, true);
            } elseif (in_array($name, self::DATETIME_FIELDS, true)) {
                $value = new DateTimeImmutable($value);
            }
        }
        return $value;
    }

    public function __set($name, $value)
    {
        if ($value && in_array($name, self::ARRAY_FIELDS, true)) {
            $value = json_encode($value);
        } elseif ($value instanceof DateTimeImmutable && in_array($name, self::DATETIME_FIELDS, true)) {
            $value = $value->format(DATE_ATOM);
        }
        $this->object->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->object->$name);
    }

    abstract public static function getObjectClass(): string;

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        $data = $this->object->toArray();
        foreach ($data as $key => $value) {
            if ($value) {
                if (in_array($key, self::ARRAY_FIELDS, true)) {
                    $data[$key] = json_decode($value, true);
                } elseif (in_array($key, self::DATETIME_FIELDS, true)) {
                    $data[$key] = new DateTimeImmutable($value);
                }
            }
        }
        return $data;
    }

    public function getObject(): xPDOObject
    {
        return $this->object;
    }

}