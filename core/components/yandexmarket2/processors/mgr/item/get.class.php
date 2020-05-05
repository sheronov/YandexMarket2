<?php

class YandexMarket2ItemGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'YandexMarket2Item';
    public $classKey = 'YandexMarket2Item';
    public $languageTopics = ['yandexmarket2:default'];
    //public $permission = 'view';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return mixed
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return parent::process();
    }

}

return 'YandexMarket2ItemGetProcessor';