<?php

use YandexMarket\Models\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'ym_pricelist';
    public $classKey       = ymPricelist::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = trim($this->getProperty('name'));
        if (empty($id)) {
            return $this->modx->lexicon('ym_pricelist_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym_pricelist_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym_pricelist_err_ae'));
        }
        $this->unsetProperty('created_on');
        $this->setProperty('edited_on', date('Y-m-d H:i:s'));
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        $this->unsetProperty('need_generate');

        return parent::beforeSet();
    }

    public function cleanup(): array
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray());
    }
}

return ymPricelistUpdateProcessor::class;
