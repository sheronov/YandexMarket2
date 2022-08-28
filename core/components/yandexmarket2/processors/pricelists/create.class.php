<?php

use YandexMarket\Handlers\PricelistFiller;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

/**
 * @property Pricelist|ymPricelist $object
 */
class ymPricelistCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'yandexmarket2.pricelist';
    public $classKey       = ymPricelist::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        if (empty($this->getProperty('name', ''))) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_pricelist_name_err_ns'));
        } elseif (empty($this->getProperty('type', ''))) {
            $this->modx->error->addField('type', $this->modx->lexicon('ym2_pricelist_type_err_ns'));
        }
        if (!$this->getProperty('file')) {
            $this->setProperty('file', 'pricelist-'.date('Y-m-d-H-i-s').'.xml');
        }
        $this->object->set('created_on', date('Y-m-d H:i:s'));
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);

        if (!$this->getProperty('class')) {
            $this->setProperty('class', $this->modx->getOption('ym2_default_pricelist_class', null,
                Service::hasMiniShop2() ? 'msProduct' : 'modDocument'));
        }

        if (!$this->getProperty('generate_mode')) {
            $this->setProperty('generate_mode', Pricelist::GENERATE_MODE_MANUALLY);
        }

        if ($this->modx->getCount($this->classKey, ['file' => $this->getProperty('file')])) {
            $this->modx->error->addField('file', $this->modx->lexicon('ym2_pricelist_file_err_ae'));
        }

        return parent::beforeSet();
    }

    public function afterSave(): bool
    {
        $pricelist = new Pricelist($this->modx, $this->object);

        $pricelistFiller = new PricelistFiller($pricelist);
        $pricelistFiller->fillDefaultValues();
        $pricelistFiller->fillDefaultConditions();

        $this->object = $pricelist;

        return parent::afterSave();
    }

    public function cleanup(): array
    {
        return $this->success('', $this->object->toArray());
    }

}

return ymPricelistCreateProcessor::class;
