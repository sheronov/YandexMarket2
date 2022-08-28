<?php

namespace YandexMarket\Processors\Pricelists;

use MODX\Revolution\Processors\ModelProcessor;
use YandexMarket\Model\YmPricelist;

class Disable extends ModelProcessor
{
    public $objectType     = 'ym2_pricelist';
    public $classKey       = YmPricelist::class;
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
            return $this->failure($this->modx->lexicon('ym2_pricelist_err_nf'));
        }

        foreach ($ids as $id) {
            /** @var YmPricelist $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('ym2_pricelist_err_nf'));
            }

            $object->set('active', false);
            $object->save();
        }

        return $this->success();
    }

}
