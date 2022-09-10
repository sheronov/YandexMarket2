<?php

namespace YandexMarket\Processors\Lists;

use MODX\Revolution\Processors\Processor;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AClassKeysProcessor extends \modProcessor { }
} else {
    abstract class AClassKeysProcessor extends Processor { }
}

class ClassKeys extends AClassKeysProcessor
{
    /** @var Service */
    protected $service;

    /**
     * @return bool
     */
    public function initialize(): bool
    {
        $this->service = new Service($this->modx);
        return parent::initialize();
    }

    public function process()
    {
        return $this->outputArray($this->service->listClassKeys(true,true));
    }

}
