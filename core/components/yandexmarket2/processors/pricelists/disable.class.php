<?php

class ymPricelistDisableProcessor extends modObjectProcessor
{
    public $objectType     = 'ym_pricelist';
    public $classKey       = ymPricelist::class;
    public $languageTopics = ['yandexmarket2'];
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
            return $this->failure($this->modx->lexicon('ym_pricelist_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var ymPricelist $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('ym_pricelist_err_nf'));
            }

            $object->set('active', false);
            $object->save();
        }

        return $this->success();
    }

}

return ymPricelistDisableProcessor::class;
