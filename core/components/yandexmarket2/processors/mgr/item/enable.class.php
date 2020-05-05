<?php

class YandexMarket2ItemEnableProcessor extends modObjectProcessor
{
    public $objectType = 'YandexMarket2Item';
    public $classKey = 'YandexMarket2Item';
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
            return $this->failure($this->modx->lexicon('yandexmarket2_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var YandexMarket2Item $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('yandexmarket2_item_err_nf'));
            }

            $object->set('active', true);
            $object->save();
        }

        return $this->success();
    }

}

return 'YandexMarket2ItemEnableProcessor';
