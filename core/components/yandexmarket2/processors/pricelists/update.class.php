<?php

use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'yandexmarket2.pricelist';
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

        if ($this->object->get('active') && $this->object->get('generated_on')) {
            $this->object->set('need_generate', true);
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
        $file = trim($this->getProperty('file'));
        if (empty($id)) {
            return $this->modx->lexicon('ym2_pricelist_err_nf');
        }

        if (empty($name) || empty($file)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_pricelist_name_err_ns'));
        } elseif ($this->modx->getCount($this->classKey, ['file' => $file, 'id:!=' => $id])) {
            $this->modx->error->addField('file', $this->modx->lexicon('ym2_pricelist_file_err_ae'));
        }
        $this->unsetProperty('created_on');
        $this->setProperty('edited_on', date('Y-m-d H:i:s'));
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        $this->unsetProperty('need_generate');

        if (!$this->getProperty('class')) {
            $this->setProperty('class', $this->modx->getOption('ym2_default_pricelist_class', null,
                Service::hasMiniShop2() ? 'msProduct' : 'modResource'));
        }

        return parent::beforeSet();
    }

    public function cleanup(): array
    {
        return $this->success('', (new Pricelist($this->modx, $this->object))->toArray());
    }
}

return ymPricelistUpdateProcessor::class;
