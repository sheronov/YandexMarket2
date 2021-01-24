<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use ymField;

/**
 * @property int $id
 * @property string $name
 * @property null|int $parent
 * @property int $type
 * @property int $pricelist_id
 * @property null|string $column
 * @property null|string $handler
 * @property null|array $properties
 * @property int $rank
 * @property DateTimeImmutable $created_on
 * @property bool $active
 */
class Field extends BaseObject
{
    public const TYPE_PARENT     = 0; //обёртка без своего собственного значения
    public const TYPE_OPTION     = 1; //чисто текстовое значение из column
    public const TYPE_CURRENCIES = 2; //тип валюты для яндекс маркета TODO: лучше убрать, КМК
    public const TYPE_CATEGORIES = 3; // дерево категорий

    // TODO: подумать над обработчиками типов
    public const TYPES_HANDLER = [
        self::TYPE_PARENT => [__CLASS__, 'handlerParent']
    ];

    public static function getObjectClass(): string
    {
        return ymField::class;
    }

    public function getPricelist(): Pricelist
    {
        return new Pricelist($this->xpdo, $this->object->getOne('Pricelist'));
    }

}