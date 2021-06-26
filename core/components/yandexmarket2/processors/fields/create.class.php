<?php

use YandexMarket\Handlers\PricelistFiller;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymFieldCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'yandexmarket2.field';
    public $classKey       = ymField::class;
    public $languageTopics = ['yandexmarket2'];

    //public $permission = 'save';
    /** @var null|Pricelist */
    protected $pricelist;

    protected $needReload = false;

    public function beforeSet(): bool
    {
        $name = trim($this->getProperty('name'));
        $type = $this->getProperty('type');
        $this->pricelist = Pricelist::getById((int)$this->getProperty('pricelist_id'), $this->modx);
        if (empty($name) || $type === null || !$this->pricelist) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_field_err_valid'));
        }
        $this->setProperty('created_on', date('Y-m-d H:i:s'));
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        if (is_array($this->getProperty('value'))) {
            $this->setProperty('value', json_encode($this->getProperty('value')));
        }

        return parent::beforeSet();
    }

    public function afterSave(): bool
    {
        if ($this->pricelist && $marketplace = $this->pricelist->getMarketplace()) {
            $pricelistFields = $this->pricelist->getFields(true);
            $field = new Field($this->modx, $this->object);

            $isOffers = in_array($field->type, [Field::TYPE_OFFERS, Field::TYPE_OFFERS_TRANSPARENT], true);
            $isCategories = in_array($field->type, [Field::TYPE_CATEGORIES, Field::TYPE_CATEGORIES_TRANSPARENT], true);

            if (($isOffers || $isCategories) && ($fieldsToAdd = $marketplace->getChildrenFieldsForType($field->type))
                && !array_filter($pricelistFields, static function (Field $pricelistField) use ($isOffers) {
                    return $pricelistField->type === ($isOffers ? Field::TYPE_OFFER : Field::TYPE_CATEGORY);
                })) {
                (new PricelistFiller($this->pricelist))->createFields($fieldsToAdd, $field);
                $this->needReload = true;
            }
        }
        return parent::afterSave();
    }

    public function cleanup()
    {
        return $this->success('',
            array_merge((new Field($this->modx, $this->object))->toArray(),
                $this->needReload ? ['need_reload' => true] : []));
    }
}

return ymFieldCreateProcessor::class;
