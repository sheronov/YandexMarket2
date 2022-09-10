<?php

namespace YandexMarket\Processors\Lists;

use MODX\Revolution\Processors\Processor;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AFieldsProcessor extends \modProcessor { }
} else {
    abstract class AFieldsProcessor extends Processor { }
}

class Fields extends AFieldsProcessor
{
    /** @var Service */
    protected $service;

    public function initialize(): bool
    {
        $this->service = new Service($this->modx);
        return parent::initialize();
    }

    public function process()
    {
        return $this->outputArray($this->service->getAvailableFields());
    }

}
