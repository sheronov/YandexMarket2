<?php

class ymConditionRemoveProcessor extends modObjectProcessor
{
    public $objectType     = 'ym_condition';
    public $classKey       = ymCondition::class;
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
            return $this->failure($this->modx->lexicon('ym_condition_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var ymCondition $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('ym_condition_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return ymConditionRemoveProcessor::class;