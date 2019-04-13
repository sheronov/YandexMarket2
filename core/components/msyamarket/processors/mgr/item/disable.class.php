<?php

class msYaMarketItemDisableProcessor extends modObjectProcessor
{
    public $objectType = 'msYaMarketItem';
    public $classKey = 'msYaMarketItem';
    public $languageTopics = ['msyamarket'];
    //public $permission = 'save';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('msyamarket_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var msYaMarketItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('msyamarket_item_err_nf'));
            }

            $object->set('active', false);
            $object->save();
        }

        return $this->success();
    }

}

return 'msYaMarketItemDisableProcessor';
