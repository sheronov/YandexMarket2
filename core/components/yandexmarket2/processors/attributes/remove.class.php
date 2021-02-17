<?php

class ymAttributeRemoveProcessor extends modObjectProcessor
{
    public $objectType     = 'ym_attribute';
    public $classKey       = 'ymFieldAttribute';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

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
            return $this->failure($this->modx->lexicon('ym_attribute_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var ymFieldAttribute $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('ym_attribute_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return ymAttributeRemoveProcessor::class;