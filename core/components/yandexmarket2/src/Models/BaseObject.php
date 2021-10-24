<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use modX;
use xPDOObject;

abstract class BaseObject
{
    protected $modx;
    protected $xpdo;
    protected $object;

    public $data = []; //для хранения временных значений при каких-либо манипуляциях

    const DATETIME_FIELDS = ['created_on', 'edited_on', 'generated_on'];
    const ARRAY_FIELDS    = ['properties'];

    public function __construct(modX $modx, xPDOObject $object = null)
    {
        $objectClass = static::getObjectClass();
        /** @var xPDOObject $newObj */
        if (!$object && $newObj = $modx->newObject($objectClass)) {
            $object = $newObj;
        }
        if (!$object || !is_a($object, $objectClass)) {
            throw new InvalidArgumentException("You must provide ".$objectClass.' entity');
        }
        $this->object = $object;
        $this->modx = $modx;
    }

    /**
     * Класс xPDO объекта, который будем оборачивать
     *
     * @return string
     */
    abstract public static function getObjectClass(): string;

    public function getObject(): xPDOObject
    {
        return $this->object;
    }

    /**
     * @param  int  $id
     * @param  modX  $modX
     *
     * @return static|null
     */
    public static function getById(int $id, modX $modX)
    {
        $object = $modX->getObject(static::getObjectClass(), $id);
        return $object ? new static($modX, $object) : null;
    }

    /**
     * @param $name
     *
     * @return array|DateTimeImmutable|mixed|xPDOObject|null
     * @throws Exception
     */
    public function __get($name)
    {
        if ($value = $this->object->{$name}) {
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
        $this->object->{$name} = $value;
    }

    public function __isset($name): bool
    {
        return isset($this->object->{$name});
    }

    public function get(string $field)
    {
        return $this->object->get($field);
    }

    public function save(): bool
    {
        return $this->object->save();
    }

    public function remove(): bool
    {
        return $this->object->remove();
    }

    public function toArray(): array
    {
        $data = $this->object->toArray();
        foreach ($data as $key => $value) {
            if ($value) {
                if (!is_array($value) && in_array($key, self::ARRAY_FIELDS, true)) {
                    $data[$key] = json_decode($value, true);
                } elseif (in_array($key, self::DATETIME_FIELDS, true)) {
                    try {
                        $data[$key] = $value;
                        // $data[$key] = new DateTimeImmutable($value);
                    } catch (Exception $exception) {
                        $this->modx->log(modX::LOG_LEVEL_ERROR, "[YandexMarket] wrong datetime {$key} = {$value}");
                        $data[$key] = $value;
                    }
                }
            }
        }
        return $data;
    }

}
