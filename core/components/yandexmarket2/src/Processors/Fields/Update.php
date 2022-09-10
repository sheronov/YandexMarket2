<?php

namespace YandexMarket\Processors\Fields;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use YandexMarket\Model\YmField;
use YandexMarket\Models\Field;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AUpdate extends \modObjectUpdateProcessor
    {
        public $classKey = \YmField::class;
    }
} else {
    abstract class AUpdate extends UpdateProcessor
    {
        public $classKey = YmField::class;
    }
}

class Update extends AUpdate
{
    public $objectType     = 'ym2_field';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = trim($this->getProperty('name'));
        $type = $this->getProperty('type');
        $pricelistId = (int)$this->getProperty('pricelist_id');
        if (empty($id) || empty($name) || $type === null || empty($pricelistId)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_field_err_valid'));
        }
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        if (is_array($this->getProperty('value'))) {
            $this->setProperty('value', json_encode($this->getProperty('value')));
        }
        $this->unsetProperty('created_on');

        return parent::beforeSet();
    }

    public function cleanup()
    {
        $field = new Field($this->modx, $this->object);
        $source = $field->getPricelist()->getMarketplace();
        $this->modx->lexicon->load($source::getLexiconNs());
        return $this->success('', $field->toArray());
    }
}
