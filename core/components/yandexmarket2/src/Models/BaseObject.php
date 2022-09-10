<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use YandexMarket\Service;

if (!defined('MODX3')) {
    define('MODX3', class_exists('MODX\Revolution\modX'));
}

abstract class BaseObject
{
    protected $modx;
    protected $xpdo;
    protected $object;

    public $data = []; //для хранения временных значений при каких-либо манипуляциях

    const DATETIME_FIELDS = ['created_on', 'edited_on', 'generated_on'];
    const ARRAY_FIELDS    = ['properties'];

    /**
     * @param  \MODX\Revolution\modX|\modX  $modx
     * @param  \xPDO\Om\xPDOObject|\xPDOObject|null  $object
     */
    public function __construct($modx, $object = null)
    {
        $objectClass = static::getObjectClass();
        /** @var \xPDO\Om\xPDOObject|\xPDOObject $newObj */
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

    /**
     * @return \xPDO\Om\xPDOObject|\xPDOObject
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param  int  $id
     * @param  \MODX\Revolution\modX|\modX $modx
     *
     * @return static|null
     */
    public static function getById(int $id, $modx)
    {
        $object = $modx->getObject(static::getObjectClass(), $id);
        return $object ? new static($modx, $object) : null;
    }

    /**
     * @param $name
     *
     * @return array|DateTimeInterface|mixed|\xPDO\Om\xPDOObject|\xPDOObject|null
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
        } elseif ($value instanceof DateTimeInterface && in_array($name, self::DATETIME_FIELDS, true)) {
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
                        $this->modx->log(Service::LOG_LEVEL_ERROR, "[YandexMarket] wrong datetime {$key} = {$value}");
                        $data[$key] = $value;
                    }
                }
            }
        }
        return $data;
    }

    protected function getClassByAlias(string $alias): string
    {
        $definition = $this->object->getFKDefinition($alias);
        return !empty($definition) ? $definition['class'] : '';
    }

}
